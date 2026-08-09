<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function is_dir;
use function mkdir;
use function unlink;

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

    /** @param list<string> $options */
    public function configure(array $options): void
    {
        $this->process->command(['./buildconf', '--force'], $this->path());
        $this->process->command(['./configure', ...$options], $this->path());
    }

    /** @param list<string> $paths */
    public function copy(array $paths): void
    {
        $archive = $this->temporary->path() . '/copy.tar';
        $this->process->command(['tar', '-cf', $archive, ...$paths], $this->repo);
        $this->process->command(['tar', '-xf', $archive, '-C', $this->path()]);
        unlink($archive);
    }
}
