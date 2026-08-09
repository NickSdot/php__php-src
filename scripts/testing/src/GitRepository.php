<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function array_keys;
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

    public function updateWorktree(string $revision, string $tree): void
    {
        if (is_dir($tree) === false) {

            $this->process->command(['git', '-C', $this->path, 'worktree', 'prune']);

            $this->process->command([
                'git', '-C', $this->path, 'worktree', 'add', '--detach', '--quiet', $tree, $revision,
            ]);

            return;
        }

        $current = trim($this->process->command(['git', '-C', $tree, 'rev-parse', 'HEAD']));

        if ($current === $revision) {
            return;
        }

        $this->process->command(['git', '-C', $tree, 'checkout', '--detach', '--force', '--quiet', $revision]);
    }

    public function behindWarning(string $base): ?string
    {
        $branch = trim($this->process->command([
            'git', '-C', $this->path, 'rev-parse', '--symbolic-full-name', $base,
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
    public function changedPathsSince(string $base): array
    {
        $paths = [];

        $changed = $this->process->command([
            'git', '-C', $this->path, 'diff', '--name-only', '-z', $base, '--',
        ]);

        foreach ($this->nullSeparated($changed) as $path) {
            $paths[$path] = true;
        }

        $untracked = $this->process->command([
            'git', '-C', $this->path, 'ls-files', '--others', '--exclude-standard', '-z',
        ]);

        foreach ($this->nullSeparated($untracked) as $path) {
            $paths[$path] = true;
        }

        $paths = array_keys($paths);
        sort($paths);

        return $paths;
    }

    /** @return list<string> */
    public function files(string $tree): array
    {
        $files = $this->nullSeparated($this->process->command([
            'git', '-C', $tree, 'ls-files', '--cached', '--others', '--exclude-standard', '-z',
        ]));

        sort($files);

        return $files;
    }

    public function diff(string $base, string $tree): string
    {
        return $this->process->command([
            'git', 'diff', '--no-index', '--no-ext-diff', '--no-color', '--unified=0', '--', $base, $tree,
        ], successfulStatuses: [0, 1]);
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
