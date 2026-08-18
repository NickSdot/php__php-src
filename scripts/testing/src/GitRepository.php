<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function array_chunk;
use function array_keys;
use function count;
use function explode;
use function is_dir;
use function realpath;
use function str_starts_with;
use function substr;
use function sort;
use function trim;

final class GitRepository
{
    public function __construct(
        private string $path,
        private ProcessRunner $process
    ) {}

    public static function discover(string $from, ProcessRunner $process): self
    {
        $path = trim($process->command(['git', '-C', $from, 'rev-parse', '--show-toplevel']));

        return new self($path, $process);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function resolve(string $revision): string
    {
        return trim($this->process->command([
            'git', '-C', $this->path, 'rev-parse', '--verify', "$revision^{commit}",
        ]));
    }

    public function commonDirectory(): string
    {
        $directory = trim($this->process->command([
            'git', '-C', $this->path, 'rev-parse', '--git-common-dir',
        ]));

        $realPath = realpath(
            Path::absolute($directory, $this->path)
        );

        if ($realPath === false) {
            throw new RuntimeException('Git directory not found');
        }

        return $realPath;
    }

    public function updateWorktree(string $revision, string $directory): void
    {
        if (is_dir($directory) === false) {

            $this->process->command(['git', '-C', $this->path, 'worktree', 'prune']);

            $this->process->command([
                'git', '-C', $this->path, 'worktree', 'add', '--detach', '--quiet', $directory, $revision,
            ]);

            return;
        }

        $current = trim($this->process->command(['git', '-C', $directory, 'rev-parse', 'HEAD']));

        if ($current === $revision) {
            return;
        }

        $this->process->command(['git', '-C', $directory, 'checkout', '--detach', '--force', '--quiet', $revision]);
    }

    public function behindWarning(string $baseReference): ?string
    {
        $branch = trim($this->process->command([
            'git', '-C', $this->path, 'rev-parse', '--symbolic-full-name', $baseReference,
        ]));

        if (str_starts_with($branch, 'refs/heads/') === false) {
            return null;
        }

        $upstream = trim($this->process->command([
            'git', '-C', $this->path, 'for-each-ref', '--format=%(upstream)', $branch,
        ]));

        if ($upstream === '') {
            return null;
        }

        $upstreamCommit = trim($this->process->command([
            'git', '-C', $this->path, 'for-each-ref', '--format=%(objectname)', $upstream,
        ]));

        if ($upstreamCommit === '') {
            return null;
        }

        $branchName = trim($this->process->command([
            'git', '-C', $this->path, 'for-each-ref', '--format=%(refname:short)', $branch,
        ]));

        $upstreamName = trim($this->process->command([
            'git', '-C', $this->path, 'for-each-ref', '--format=%(refname:short)', $upstream,
        ]));

        $behind = (int) trim($this->process->command([
            'git', '-C', $this->path, 'rev-list', '--count', "$branch..$upstream",
        ]));

        if ($behind === 0) {
            return null;
        }

        return "$branchName is behind $upstreamName; results may be stale. Update it or pass --base.";
    }

    /** @return list<string> */
    public function changedPaths(string $baseRevision, ?string $treeRevision = null): array
    {
        return $this->changedPathsFrom($baseRevision, $treeRevision);
    }

    /** @return list<string> */
    public function changedPathsSince(string $baseRevision, ?string $treeRevision = null): array
    {
        return $this->changedPathsFrom(
            $this->mergeBase($baseRevision, $treeRevision ?? 'HEAD'),
            $treeRevision
        );
    }

    /** @return list<string> */
    private function changedPathsFrom(string $baseRevision, ?string $treeRevision): array
    {
        $paths = [];

        foreach ($this->diffPaths($baseRevision, $treeRevision) as $path) {
            $paths[$path] = true;
        }

        if ($treeRevision === null) {
            $untracked = $this->process->command([
                'git', '-C', $this->path, 'ls-files', '--others', '--exclude-standard', '-z',
            ]);

            foreach ($this->nullSeparated($untracked) as $path) {
                $paths[$path] = true;
            }
        }

        $paths = array_keys($paths);
        sort($paths);

        return $paths;
    }

    private function mergeBase(string $baseRevision, string $treeRevision): string
    {
        return trim($this->process->command([
            'git', '-C', $this->path, 'merge-base', $baseRevision, $treeRevision,
        ]));
    }

    /** @return list<string> */
    public function deletedPaths(string $baseRevision, ?string $treeRevision = null): array
    {
        return $this->diffPaths($baseRevision, $treeRevision, 'D');
    }

    /** @return array<string, string> */
    public function renamedPaths(string $baseRevision, ?string $treeRevision = null): array
    {
        $command = [
            'git', '-C', $this->path, 'diff', '--name-status', '-z',
            '--find-renames', '--diff-filter=R', $baseRevision,
        ];

        if ($treeRevision !== null) {
            $command[] = $treeRevision;
        }

        $command[] = '--';

        $entries = $this->nullSeparated($this->process->command($command));

        if (count($entries) % 3 !== 0) {
            throw new RuntimeException('Could not parse renamed paths');
        }

        $renamedPaths = [];

        foreach (array_chunk($entries, 3) as [$status, $basePath, $treePath]) {

            if (str_starts_with($status, 'R') === false) {
                throw new RuntimeException('Could not parse renamed paths');
            }

            $renamedPaths[$basePath] = $treePath;
        }

        return $renamedPaths;
    }

    /** @return list<string> */
    public function files(string $directory): array
    {
        $files = $this->nullSeparated($this->process->command([
            'git', '-C', $directory, 'ls-files', '--cached', '--others', '--exclude-standard', '-z',
        ]));

        sort($files);

        return $files;
    }

    public function diff(string $baseFile, string $treeFile): string
    {
        return $this->process->command([
            'git', 'diff',
            '--no-index', '--no-ext-diff', '--no-color', '--text', '--unified=0',
            '--', $baseFile, $treeFile,
        ], successfulStatuses: [0, 1]);
    }

    /** @return list<string> */
    private function diffPaths(string $baseRevision, ?string $treeRevision, ?string $filter = null): array
    {
        $command = ['git', '-C', $this->path, 'diff', '--name-only', '-z'];

        if ($filter !== null) {
            $command[] = "--diff-filter=$filter";
        }

        $command[] = $baseRevision;

        if ($treeRevision !== null) {
            $command[] = $treeRevision;
        }

        $command[] = '--';

        $paths = $this->nullSeparated($this->process->command($command));
        sort($paths);

        return $paths;
    }

    /** @return list<string> */
    private function nullSeparated(string $output): array
    {
        if ($output === '') {
            return [];
        }

        return explode("\0", substr($output, 0, -1));
    }
}
