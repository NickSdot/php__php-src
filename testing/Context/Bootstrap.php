<?php

namespace TestSuite\Context;

use function getenv;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Share.php';

final class Bootstrap
{
    public static function install(array &$iniSettings): ?string
    {
        $bootstrapFile = __DIR__ . DIRECTORY_SEPARATOR . 'prepend.php';

        $autoPrependFile = $iniSettings['auto_prepend_file'] ?? null;

        if ($autoPrependFile === $bootstrapFile) {
            $autoPrependFile = null;
        }

        $iniSettings['auto_prepend_file'] = $bootstrapFile;

        return $autoPrependFile;
    }

    public static function forwardAutoPrepend(array &$environment, ?string $autoPrependFile): void
    {
        if ($autoPrependFile === null || $autoPrependFile === '') {
            unset($environment['PHP_TEST_CONTEXT_AUTO_PREPEND_FILE']);
            return;
        }

        $environment['PHP_TEST_CONTEXT_AUTO_PREPEND_FILE'] = $autoPrependFile;
    }

    public static function restoreVariables(): void
    {
        if (getenv('PHP_TEST_CONTEXT_RESTORE_VARIABLES') === '1') {
            Share::set();
        }
    }

    public static function originalAutoPrependFile(): ?string
    {
        $autoPrependFile = getenv('PHP_TEST_CONTEXT_AUTO_PREPEND_FILE');

        return $autoPrependFile === false || $autoPrependFile === ''
            ? null
            : $autoPrependFile;
    }
}
