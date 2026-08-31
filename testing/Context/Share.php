<?php

namespace TestSuite\Context;

use TestSuite\Context\Filesystem\Directory;
use TestSuite\Context\Filesystem\File;
use ValueError;

use function array_key_exists;
use function getenv;
use function is_array;
use function is_int;
use function is_scalar;
use function is_string;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'WorkspaceError.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestContext.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Filesystem' . DIRECTORY_SEPARATOR . 'File.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Filesystem' . DIRECTORY_SEPARATOR . 'Directory.php';

final class Share
{
    public static function put(string|array $variables): void
    {
        $context = TestContext::instance();

        try {

            if (getenv('PHP_TEST_CONTEXT_SETUP') !== '1') {
                throw new WorkspaceError('Workspace::share() is only available in SETUP');
            }

            if ($context->var !== []) {
                throw new WorkspaceError('Workspace::share() may only be called once');
            }

            $variables = (array) $variables;

            if ($variables === []) {
                throw new ValueError('Shared variables cannot be empty');
            }

            $shared = [];

            foreach ($variables as $name => $value) {

                if (!is_int($name)) {

                    if ($name === '') {
                        throw new ValueError('Shared variable names must be non-empty strings');
                    }

                    self::validateValue($value, $name);

                    $shared[$name] = $value;
                    continue;
                }

                $name = $value;

                if (!is_string($name) || $name === '') {
                    throw new ValueError('Shared variable names must be non-empty strings');
                }

                if (!array_key_exists($name, $GLOBALS)) {
                    throw new WorkspaceError("Undefined variable: $$name");
                }

                self::validateValue($GLOBALS[$name], $name);

                $shared[$name] = $GLOBALS[$name];
            }

            $context->allocateVar($shared);

        } catch (WorkspaceError|ValueError $error) {
            $context->recordFailure($error->getMessage());
            throw $error;
        }
    }

    public static function set(): void
    {
        foreach (TestContext::instance()->var as $name => $value) {
            $GLOBALS[$name] = $value;
        }
    }

    private static function validateValue(mixed $value, string $name): void
    {
        if ($value === null || is_scalar($value) || $value instanceof File || $value instanceof Directory) {
            return;
        }

        if (!is_array($value)) {
            throw new ValueError("Variable $$name cannot be shared");
        }

        foreach ($value as $item) {
            self::validateValue($item, $name);
        }
    }
}
