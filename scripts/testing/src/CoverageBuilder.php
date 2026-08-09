<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function escapeshellarg;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function getenv;
use function is_file;
use function ltrim;
use function mkdir;
use function rtrim;
use function str_replace;
use function str_starts_with;
use function trim;

final class CoverageBuilder
{
    private const PHP = '/sapi/cli/php';
    private const BUILT_REVISION_FILE = '.built';
    private const CONFIGURED_FILE = '.configured';

    public function __construct(
        private GitRepository $repository,
        private ProcessRunner $process,
        private Output $output,
        private string $gcov
    ) {}

    public function build(TestCoverageOptions $options, string $baseRevision, string $temporary): CoverageRuntimes
    {
        $configuration = $this->configuration(
            $repo = $this->repository->path()
         );

        $treeCache = new CoverageBuildCache(CoverageBuildCache::key(
            $repo,
            'tree',
            $this->normaliseConfiguration($configuration, $repo)
        ));

        $treeRoot = $treeCache->directory(function (string $directory): void {
            $this->createBuildDirectory($directory);
        });

        $treeBuild = "$treeRoot/build";

        $configurationState = new BuildConfiguration($this->repository);
        $treeConfiguration = $configurationState->fingerprint($repo, $configuration);

        $this->prepare($configuration, $repo, $treeBuild, $treeConfiguration);

        $this->make('tree', $treeBuild);

        $tree = $this->runtime($treeBuild, $repo, $temporary);
        $changedPaths = $this->repository->changedPathsSince($baseRevision);

        $cache = new CoverageBuildCache(CoverageBuildCache::key(
            $this->repository->commonDirectory(),
            $options->base,
            $this->normaliseConfiguration($configuration, $repo)
        ));

        $baseRoot = $cache->directory(function (string $directory): void {
            $this->createBuildDirectory($directory);
        });

        $baseSource = "$baseRoot/source";
        $baseBuild = "$baseRoot/build";

        $this->repository->updateWorktree($baseRevision, $baseSource);

        $baseConfiguration = $configurationState->fingerprint($baseSource, $configuration);

        if ($baseConfiguration === $treeConfiguration && $tree->dependencies->affectedSources($changedPaths) === []) {
            return new CoverageRuntimes($tree, $tree, $baseSource, $repo, $changedPaths);
        }

        if ($this->prepareBase($baseRevision, $configuration, $baseSource, $baseBuild, $baseConfiguration) === true) {
            $this->make('base', $baseBuild);
            $this->recordBuiltRevision($baseBuild, $baseRevision);
        }

        return new CoverageRuntimes(
            $this->runtime($baseBuild, $baseSource, $temporary),
            $tree,
            $baseSource,
            $repo,
            $changedPaths
        );
    }

    private function configuration(string $repo): string
    {
        $configuration = file_get_contents("$repo/config.nice");

        if ($configuration === false) {
            throw new RuntimeException('Build configuration is unavailable. Configure PHP before running coverage.');
        }

        return $configuration;
    }

    private function normaliseConfiguration(string $configuration, string $repo): string
    {
        return str_replace($repo, '{source}', $configuration);
    }

    private function prepareBase(string $revision, string $configuration, string $source, string $build, string $fingerprint): bool
    {
        $built = null;
        $file = $this->builtRevisionFile($build);

        if (is_file($file) === true) {
            $built = file_get_contents($file);

            if ($built === false) {
                throw new RuntimeException('Could not read base build revision');
            }
        }

        $configured = $this->prepare($configuration, $source, $build, $fingerprint);

        return $configured === true || $built !== $revision || is_file($build . self::PHP) === false;
    }

    private function recordBuiltRevision(string $build, string $revision): void
    {
        if (file_put_contents($this->builtRevisionFile($build), $revision) === false) {
            throw new RuntimeException('Could not save base build revision');
        }
    }

    private function builtRevisionFile(string $build): string
    {
        return "$build/" . self::BUILT_REVISION_FILE;
    }

    private function prepare(string $configuration, string $source, string $build, string $fingerprint): bool
    {
        $file = "$build/" . self::CONFIGURED_FILE;

        if (is_file("$build/Makefile") === true && is_file($file) === true) {
            $configured = file_get_contents($file);

            if ($configured === false) {
                throw new RuntimeException('Could not read build configuration state');
            }

            if ($configured === $fingerprint) {
                return false;
            }
        }

        $this->configure($configuration, $source, $build);

        if (file_put_contents($file, $fingerprint) === false) {
            throw new RuntimeException('Could not save build configuration state');
        }

        return true;
    }

    private function configure(string $configuration, string $source, string $build): void
    {
        $this->process->command(['./buildconf', '--force'], $source);

        $configuration = $this->coverageConfiguration($configuration, $source);

        if (file_put_contents("$build/config.nice", $configuration) === false) {
            throw new RuntimeException('Could not prepare coverage build');
        }

        $this->process->command(['sh', './config.nice', '--enable-gcov'], $build);
    }

    private function coverageConfiguration(string $configuration, string $source): string
    {
        $lines = explode("\n", rtrim($configuration, "\r\n"));

        foreach ($lines as $index => $line) {

            $value = trim($line);

            if ($value === '' || str_starts_with(ltrim($line), '#') === true) {
                continue;
            }

            $lines[$index] = escapeshellarg("$source/configure") . ' \\';
            return $this->output->lines($lines);
        }

        throw new RuntimeException('Build configuration is invalid. Run configure before coverage.');
    }

    private function createBuildDirectory(string $directory): void
    {
        if (mkdir("$directory/build") === false) {
            throw new RuntimeException('Could not create coverage build directory');
        }
    }

    private function make(string $name, string $directory): void
    {
        $this->output->printLine('Building %s', $name);
        $this->process->command([$this->makeCommand(), '-j' . TestCoverageCommand::WORKERS, 'cli'], $directory);
    }

    private function runtime(string $directory, string $source, string $temporary): CoverageRuntime
    {
        $coverage = new GcovCoverage($directory, $source, $this->gcov, $this->process);
        $coverage->validateBuild();

        // todo: include standalone phpize extension builds
        // once the validator can build and load them.
        $dependencies = (new BuildDependencyReader())->read($directory, $source);

        return new CoverageRuntime(
            $coverage,
            new PhptRunner(
                $this->process,
                $this->output,
                Path::absoluteFile($directory . self::PHP, $this->repository->path()),
                $temporary
            ),
            $dependencies
        );
    }

    private function makeCommand(): string
    {
        $make = getenv('MAKE');

        if ($make === false || $make === '') {
            return 'make';
        }

        return $make;
    }
}
