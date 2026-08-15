<?php

declare(strict_types=1);

namespace PHP\Testing;

use InvalidArgumentException;
use Throwable;

use function array_merge;
use function array_slice;
use function count;
use function explode;
use function in_array;
use function str_contains;
use function str_starts_with;
use function substr;

final class TestCoverageCommand
{
    // todo(NickSdot): use auto-detection when run-tests.php is extracted
    public const WORKERS = 10;

    private const USAGE = <<<'USAGE'
    Usage:
      php scripts/testing/validate_test_coverage.php [options] [test paths...]

    Options:
      --base REF             Base branch or commit (default: master)
      --tree REF             Tree branch or commit (default: working tree)
      --source PATH          Limit source scope (repeatable)
      --global               Compare every discovered source
      -h, --help             Show help

    Examples:
      php scripts/testing/validate_test_coverage.php

      php scripts/testing/validate_test_coverage.php ext/uri/tests

      php scripts/testing/validate_test_coverage.php \
        --base upstream/master \
        --tree HEAD \
        --source ext/standard/scanf.c \
        ext/standard/tests/file ext/standard/tests/strings

      cd /path/to/php-src
      php /path/to/coverage-check/scripts/testing/validate_test_coverage.php \
        --base PHP-8.5 \
        --tree feature/ref \
        ext/standard/tests
    USAGE;

    public function __construct(
        private Output $output = new Output(),
        private ProcessRunner $process = new ProcessRunner()
    ) {}

    /** @param list<string> $arguments */
    public function run(array $arguments): int
    {
        try {
            $options = $this->options($arguments);

            if ($options->help === true) {
                $this->output->printLine(self::USAGE);
                return 0;
            }

            return (new TestCoverageValidator($this->output, $this->process))->validate($options);

        } catch (Throwable $throwable) {

            $this->output->error($throwable->getMessage());
            return 1;
        }
    }

    /** @param list<string> $arguments */
    private function options(array $arguments): TestCoverageOptions
    {
        $options = [
            'base' => 'master',
            'tree' => null,
            'sources' => [],
            'testPaths' => [],
            'global' => false,
            'help' => false,
        ];

        $index = 1;

        while ($index < count($arguments)) {

            $argument = $arguments[$index++];

            if ($argument === '--') {
                $options['testPaths'] = array_merge($options['testPaths'], array_slice($arguments, $index));
                break;
            }

            if ($argument === '-h' || $argument === '--help') {
                $options['help'] = true;
                continue;
            }

            if (str_starts_with($argument, '--') === false) {
                $options['testPaths'][] = $argument;
                continue;
            }

            $name = $this->optionName($argument);

            if (in_array($name, ['base', 'tree', 'source', 'global'], true) === false) {
                throw new InvalidArgumentException("Unknown option: --$name");
            }

            if ($name === 'global') {

                if (str_contains($argument, '=') === true) {
                    throw new InvalidArgumentException('--global does not take value');
                }

                $options['global'] = true;
                continue;
            }

            $value = $this->optionValue($name, $argument, $arguments, $index);

            if ($name === 'source') {
                $options['sources'][] = $value;
                continue;
            }

            if ($name === 'base') {
                $options['base'] = $value;
                continue;
            }

            if ($name === 'tree') {
                $options['tree'] = $value;
                continue;
            }

        }

        if ($options['global'] === true && $options['sources'] !== []) {
            throw new InvalidArgumentException('--global cannot be combined with --source');
        }

        return new TestCoverageOptions(...$options);
    }

    /**
     * @param list<string> $arguments
     */
    private function optionValue(string $name, string $argument, array $arguments, int &$index): string
    {
        $option = substr($argument, 2);

        if (str_contains($option, '=') === true) {
            [, $value] = explode('=', $option, 2);
        } else {
            $value = $arguments[$index++] ?? '';
        }

        if ($value === '' || str_starts_with($value, '--')) {
            throw new InvalidArgumentException("--$name requires value");
        }

        return $value;
    }

    private function optionName(string $argument): string
    {
        return explode('=', substr($argument, 2), 2)[0];
    }
}
