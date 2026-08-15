<?php

declare(strict_types=1);

namespace PHP\Testing;

use RuntimeException;

use function escapeshellarg;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function getenv;
use function implode;
use function is_file;
use function ltrim;
use function mkdir;
use function rtrim;
use function str_replace;
use function str_starts_with;
use function trim;

final class CoverageBuilder
{
    private const string PHP = '/sapi/cli/php';
    private const string BUILT_REVISION_FILE = '.built';
    private const string CONFIGURED_FILE = '.configured';

    private const array CONFIGURE_OPTIONS = [
        '--enable-gcov',
        '--enable-zend-test',
    ];

    public function __construct(
        private GitRepository $repository,
        private ProcessRunner $process,
        private Output $output,
        private string $gcov
    ) {}

    public function build(TestCoverageOptions $options, string $baseRevision, string $treeRevision, string $temporary): CoverageRuntimes
    {
        $configuration = $this->configuration(
            $repo = $this->repository->path()
        );

        $configurationIdentity = $this->normaliseConfiguration(
            $this->configurationIdentity($configuration),
            $repo
        );

        $commonDirectory = $this->repository->commonDirectory();

        $treeCache = new CoverageBuildCache(CoverageBuildCache::key(
            $options->tree === null ? $repo : $commonDirectory,
            'tree',
            $configurationIdentity
        ));

        $treeRoot = $treeCache->directory(function (string $directory): void {
            $this->createBuildDirectory($directory);
        });

        $treeSource = $options->tree === null ? $repo : "$treeRoot/source";
        $treeBuild = "$treeRoot/build";

        if ($options->tree !== null) {
            $this->repository->updateWorktree($treeRevision, $treeSource);
        }

        $configurationState = new BuildConfiguration($this->repository);
        $treeConfiguration = $configurationState->fingerprint($treeSource, $configurationIdentity);

        $this->prepareTree($options->tree, $treeRevision, $configuration, $treeSource, $treeBuild, $treeConfiguration);

        $tree = $this->runtime($treeBuild, $treeSource, $temporary);

        $changedPaths = $this->repository->changedPaths(
            $baseRevision,
            $options->tree === null ? null : $treeRevision
        );

        $baseCache = new CoverageBuildCache(CoverageBuildCache::key(
            $commonDirectory,
            'base',
            $configurationIdentity
        ));

        $baseRoot = $baseCache->directory(function (string $directory): void {
            $this->createBuildDirectory($directory);
        });

        $baseSource = "$baseRoot/source";
        $baseBuild = "$baseRoot/build";

        $this->repository->updateWorktree($baseRevision, $baseSource);

        $baseConfiguration = $configurationState->fingerprint($baseSource, $configurationIdentity);

        $canReuseTree = $baseConfiguration === $treeConfiguration
            && $tree->dependencies->affectedSources($changedPaths) === [];

        $deletedPaths = [];

        if ($canReuseTree === true) {

            $deletedPaths = $this->repository->deletedPaths(
                $baseRevision,
                $options->tree === null ? null : $treeRevision
            );

            if ($deletedPaths === []) {
                return new CoverageRuntimes($tree, $tree, $baseSource, $treeSource, $changedPaths);
            }
        }

        if ($this->prepareRevision($baseRevision, $configuration, $baseSource, $baseBuild, $baseConfiguration) === true) {
            $this->make('base', $baseBuild);
            $this->recordBuiltRevision($baseBuild, $baseRevision);
        }

        $base = $this->runtime($baseBuild, $baseSource, $temporary);

        if ($canReuseTree === true && $base->dependencies->affectedSources($deletedPaths) === []) {
            return new CoverageRuntimes($tree, $tree, $baseSource, $treeSource, $changedPaths);
        }

        return new CoverageRuntimes(
            $base,
            $tree,
            $baseSource,
            $treeSource,
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

    private function configurationIdentity(string $configuration): string
    {
        return $configuration . "\0" . implode("\0", self::CONFIGURE_OPTIONS);
    }

    private function prepareTree(?string $reference, string $revision, string $configuration, string $source, string $build, string $fingerprint): void
    {
        if ($reference === null) {
            $this->prepare($configuration, $source, $build, $fingerprint);
            $this->make('tree', $build);
            return;
        }

        if ($this->prepareRevision($revision, $configuration, $source, $build, $fingerprint) === false) {
            return;
        }

        $this->make('tree', $build);
        $this->recordBuiltRevision($build, $revision);
    }

    private function prepareRevision(string $revision, string $configuration, string $source, string $build, string $fingerprint): bool
    {
        $built = null;
        $file = $this->builtRevisionFile($build);

        if (is_file($file) === true) {
            $built = file_get_contents($file);

            if ($built === false) {
                throw new RuntimeException('Could not read build revision');
            }
        }

        $configured = $this->prepare($configuration, $source, $build, $fingerprint);

        return $configured === true || $built !== $revision || is_file($build . self::PHP) === false;
    }

    private function recordBuiltRevision(string $build, string $revision): void
    {
        if (file_put_contents($this->builtRevisionFile($build), $revision) === false) {
            throw new RuntimeException('Could not save build revision');
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

        $this->process->command(['sh', './config.nice', ...self::CONFIGURE_OPTIONS], $build);
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
