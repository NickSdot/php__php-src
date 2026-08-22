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
        private string $gcov,
        private int $jobs
    ) {}

    public function build(TestCoverageOptions $options, string $baseRevision, string $treeRevision, string $temporaryDirectory): CoverageRuntimes
    {
        $configurationScript = $this->readConfiguration(
            $repositoryDirectory = $this->repository->path()
        );

        $configurationIdentity = $this->configurationIdentity($configurationScript, $repositoryDirectory);

        $changedPaths = $this->repository->changedPaths(
            $baseRevision,
            $options->tree === null ? null : $treeRevision
        );

        $treeChangedPaths = $this->repository->changedPathsSince(
            $baseRevision,
            $options->tree === null ? null : $treeRevision
        );

        $baseBuild = $this->revisionBuild('base', $baseRevision, $configurationIdentity);
        $baseRuntime = $this->buildRuntime('base', $configurationScript, $baseBuild, $temporaryDirectory);

        $treeBuild = $options->tree === null
            ? $this->workingTreeBuild($configurationIdentity)
            : $this->revisionBuild('tree', $treeRevision, $configurationIdentity);

        $treeRuntime = $baseRuntime;

        if ($this->requiresTreeBuild($baseBuild, $treeBuild, $baseRuntime, $changedPaths) === true) {
            $treeRuntime = $this->buildRuntime('tree', $configurationScript, $treeBuild, $temporaryDirectory);
        }

        return new CoverageRuntimes(
            $baseRuntime,
            $treeRuntime,
            $baseBuild->sourceDirectory,
            $treeBuild->sourceDirectory,
            $treeChangedPaths
        );
    }

    private function revisionBuild(string $role, string $revision, string $configurationIdentity): CoverageBuild
    {
        $cacheDirectory = $this->buildCacheDirectory($this->repository->commonDirectory(), $role, $configurationIdentity);
        $sourceDirectory = "$cacheDirectory/source";

        $this->repository->updateWorktree($revision, $sourceDirectory);

        return $this->coverageBuild($revision, $cacheDirectory, $sourceDirectory, $configurationIdentity);
    }

    private function workingTreeBuild(string $configurationIdentity): CoverageBuild
    {
        $cacheDirectory = $this->buildCacheDirectory(
            $repositoryDirectory = $this->repository->path(),
            'tree',
            $configurationIdentity,
        );

        return $this->coverageBuild(null, $cacheDirectory, $repositoryDirectory, $configurationIdentity);
    }

    private function coverageBuild(?string $revision, string $cacheDirectory, string $sourceDirectory, string $configurationIdentity): CoverageBuild
    {
        $configurationFingerprint = (new BuildConfiguration($this->repository))->fingerprint(
            $sourceDirectory,
            $configurationIdentity
        );

        return new CoverageBuild(
            $revision,
            $sourceDirectory,
            "$cacheDirectory/build",
            $configurationFingerprint
        );
    }

    private function buildCacheDirectory(string $repositoryIdentity, string $role, string $configurationIdentity): string
    {
        return (new CoverageBuildCache(CoverageBuildCache::key(
            $repositoryIdentity,
            $role,
            $configurationIdentity
        )))->directory();
    }

    /** @param list<string> $changedPaths */
    private function requiresTreeBuild(CoverageBuild $baseBuild, CoverageBuild $treeBuild, CoverageRuntime $baseRuntime, array $changedPaths): bool
    {
        return $baseBuild->configurationFingerprint !== $treeBuild->configurationFingerprint
            || $baseRuntime->dependencies->affectedSources($changedPaths) !== [];
    }

    private function buildRuntime(string $role, string $configurationScript, CoverageBuild $build, string $temporaryDirectory): CoverageRuntime
    {
        if ($this->prepareBuild($configurationScript, $build) === false) {
            return $this->runtime($build, $temporaryDirectory);
        }

        $this->make($role, $build);

        if ($build->revision !== null) {
            $this->recordBuiltRevision($build, $build->revision);
        }

        return $this->runtime($build, $temporaryDirectory);
    }

    private function readConfiguration(string $repositoryDirectory): string
    {
        $configurationScript = file_get_contents("$repositoryDirectory/config.nice");

        if ($configurationScript === false) {
            throw new RuntimeException('Build configuration is unavailable. Configure PHP before running coverage.');
        }

        return $configurationScript;
    }

    private function configurationIdentity(string $configurationScript, string $sourceDirectory): string
    {
        $configurationScript = str_replace($sourceDirectory, '{source}', $configurationScript);

        return $configurationScript . "\0" . implode("\0", self::CONFIGURE_OPTIONS);
    }

    private function prepareBuild(string $configurationScript, CoverageBuild $build): bool
    {
        $configurationChanged = $this->prepareConfiguration($configurationScript, $build);

        if ($build->revision === null) {
            return true;
        }

        return $configurationChanged === true
            || $this->builtRevision($build) !== $build->revision
            || is_file($build->buildDirectory . self::PHP) === false;
    }

    private function builtRevision(CoverageBuild $build): ?string
    {
        $revisionFile = $this->builtRevisionFile($build);

        if (is_file($revisionFile) === false) {
            return null;
        }

        $revision = file_get_contents($revisionFile);

        if ($revision === false) {
            throw new RuntimeException('Could not read build revision');
        }

        return $revision;
    }

    private function recordBuiltRevision(CoverageBuild $build, string $revision): void
    {
        if (file_put_contents($this->builtRevisionFile($build), $revision) === false) {
            throw new RuntimeException('Could not save build revision');
        }
    }

    private function builtRevisionFile(CoverageBuild $build): string
    {
        return $build->buildDirectory . '/' . self::BUILT_REVISION_FILE;
    }

    private function prepareConfiguration(string $configurationScript, CoverageBuild $build): bool
    {
        $configurationFile = $build->buildDirectory . '/' . self::CONFIGURED_FILE;

        if ($this->configurationIsCurrent($configurationFile, $build) === true) {
            return false;
        }

        $this->configure($configurationScript, $build);

        if (file_put_contents($configurationFile, $build->configurationFingerprint) === false) {
            throw new RuntimeException('Could not save build configuration state');
        }

        return true;
    }

    private function configurationIsCurrent(string $configurationFile, CoverageBuild $build): bool
    {
        if (is_file($build->buildDirectory . '/Makefile') === false || is_file($configurationFile) === false) {
            return false;
        }

        $storedFingerprint = file_get_contents($configurationFile);

        if ($storedFingerprint === false) {
            throw new RuntimeException('Could not read build configuration state');
        }

        return $storedFingerprint === $build->configurationFingerprint;
    }

    private function configure(string $configurationScript, CoverageBuild $build): void
    {
        $this->process->command(['./buildconf', '--force'], $build->sourceDirectory);

        $configurationScript = $this->coverageConfiguration($configurationScript, $build->sourceDirectory);

        if (file_put_contents($build->buildDirectory . '/config.nice', $configurationScript) === false) {
            throw new RuntimeException('Could not prepare coverage build');
        }

        $this->process->command(['sh', './config.nice', ...self::CONFIGURE_OPTIONS], $build->buildDirectory);
    }

    private function coverageConfiguration(string $configurationScript, string $sourceDirectory): string
    {
        $configurationLines = explode("\n", rtrim($configurationScript, "\r\n"));

        foreach ($configurationLines as $index => $line) {

            $trimmedLine = trim($line);

            if ($trimmedLine === '' || str_starts_with(ltrim($line), '#') === true) {
                continue;
            }

            $configurationLines[$index] = escapeshellarg("$sourceDirectory/configure") . ' \\';
            return $this->output->lines($configurationLines);
        }

        throw new RuntimeException('Build configuration is invalid. Run configure before coverage.');
    }

    private function make(string $role, CoverageBuild $build): void
    {
        $this->output->progress('Building %s', $role);

        $this->process->command(
            [$this->makeCommand(), '-j' . $this->jobs, 'cli'],
            $build->buildDirectory
        );
    }

    private function runtime(CoverageBuild $build, string $temporaryDirectory): CoverageRuntime
    {
        $coverage = new GcovCoverage($build, $this->gcov, $this->process);

        $coverage->validateBuild();

        // todo: include standalone phpize extension builds
        // once the validator can build and load them.
        $dependencies = (new BuildDependencyReader())->read($build);

        return new CoverageRuntime(
            $coverage,
            new PhptRunner(
                $this->process,
                $this->output,
                Path::absoluteFile($build->buildDirectory . self::PHP, $this->repository->path()),
                $temporaryDirectory,
                $this->jobs
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
