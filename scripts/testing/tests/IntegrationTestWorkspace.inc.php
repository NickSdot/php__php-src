<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function file_put_contents;
use function is_dir;
use function mkdir;
use function trim;

final class IntegrationTestWorkspace
{
    private function __construct(
        private string $repo,
        private TestTemporaryDirectory $temporary,
        private ProcessRunner $process
    ) {}

    public static function create(string $repo, string $revision, string $stateFile): self
    {
        $temporary = TestTemporaryDirectory::create($stateFile);
        $directory = $temporary->path();

        if (mkdir("$directory/tmp") === false) {
            throw new RuntimeException('Could not create temporary directory');
        }

        ($process = new ProcessRunner())->command([
            'git', '-C', $repo, 'worktree', 'add', '--detach', '--quiet', "$directory/repo", $revision,
        ]);

        return new self($repo, $temporary, $process);
    }

    public static function remove(string $repo, string $stateFile): void
    {
        $temporary = TestTemporaryDirectory::fromStateFile($stateFile);

        if ($temporary === null) {
            return;
        }

        $directory = $temporary->path();
        $fixture = "$directory/repo";
        $process = new ProcessRunner();

        if (is_dir($fixture) === true) {
            $process->command(['git', '-C', $repo, 'worktree', 'remove', '--force', $fixture]);
        }

        $temporary->remove();

        $process->command(['git', '-C', $repo, 'worktree', 'prune']);
    }

    public function path(): string
    {
        return $this->temporary->path() . '/repo';
    }

    public function temporaryPath(): string
    {
        return $this->temporary->path() . '/tmp';
    }

    public function write(string $path, string $contents): void
    {
        $file = $this->path() . "/$path";

        if (file_put_contents($file, $contents) === false) {
            throw new RuntimeException("Could not write fixture: $path");
        }
    }

    public function commit(string $message): string
    {
        $this->process->command(['git', 'add', '--all'], $this->path());

        $this->process->command([
            'git', '-c', 'user.name=PHP', '-c', 'user.email=php@example.com',
            'commit', '--quiet', '-m', $message,
        ], $this->path());

        return trim($this->process->command(['git', 'rev-parse', 'HEAD'], $this->path()));
    }

    /** @param list<string> $options */
    public function configure(array $options): void
    {
        $this->process->command(['./buildconf', '--force'], $this->path());
        $this->process->command(['./configure', ...$options], $this->path());
    }
}
