<?php

namespace TestSuite\Context;

use Stringable;
use ValueError;

use function getenv;
use function in_array;
use function is_array;
use function is_scalar;
use function parse_ini_string;
use function strpbrk;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'WorkspaceError.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestContext.php';

final class Ini
{
    public static function put(array|string $settings): void
    {
        $context = TestContext::instance();

        try {

            if (getenv('PHP_TEST_CONTEXT_SETUP') !== '1') {
                throw new WorkspaceError('Workspace::ini() is only available in SETUP');
            }

            if ($context->ini !== []) {
                throw new WorkspaceError('Workspace::ini() may only be called once');
            }

            $context->allocateIni(self::normalise($settings));

        } catch (WorkspaceError|ValueError $e) {
            $context->recordFailure($e->getMessage());
            throw $e;
        }
    }

    private static function normalise(array|string $settings): array
    {
        if (is_string($settings)) {

            $settings = @parse_ini_string($settings, false, INI_SCANNER_RAW);

            if ($settings === false) {
                throw new ValueError('Invalid INI settings');
            }
        }

        if ($settings === []) {
            throw new ValueError('INI settings cannot be empty');
        }

        $normalised = [];

        foreach ($settings as $name => $value) {

            if (!is_string($name) || $name === '') {
                throw new ValueError('INI setting names must be non-empty strings');
            }

            if (strpbrk($name, "=\r\n\0") !== false) {
                throw new ValueError("Invalid INI setting name: $name");
            }

            if (is_array($value) && $value === []) {
                throw new ValueError("Invalid INI value for $name");
            }

            if ($value === null) {
                $normalised[$name] = '';
                continue;
            }

            if (is_scalar($value) || $value instanceof Stringable) {
                $normalised[$name] = (string) $value;
                continue;
            }

            if (!is_array($value) || !in_array($name, ['extension', 'zend_extension'], true)) {
                throw new ValueError("Invalid INI value for $name");
            }

            $normalised[$name] = array_map(
                static fn (mixed $item): string => is_scalar($item) || $item instanceof Stringable
                    ? (string) $item
                    : throw new ValueError("Invalid INI value for $name"),
                $value,
            );
        }

        return $normalised;
    }
}
