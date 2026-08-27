<?php

namespace TestSuite\Context;

final class Bootstrap
{
    public static function installFile(array &$iniSettings): ?string
    {
        return self::install($iniSettings, 'bootstrap-file.php');
    }

    public static function installSetup(array &$iniSettings): ?string
    {
        return self::install($iniSettings, 'bootstrap-setup.php');
    }

    private static function install(array &$iniSettings, string $file): ?string
    {
        $bootstrapFile = __DIR__ . DIRECTORY_SEPARATOR . $file;

        $autoPrependFile = $iniSettings['auto_prepend_file'] ?? null;

        if ($autoPrependFile === $bootstrapFile) {
            $autoPrependFile = null;
        }

        $iniSettings['auto_prepend_file'] = $bootstrapFile;

        return $autoPrependFile;
    }

    public static function configureEnvironment(array &$environment, ?string $autoPrependFile = null): void
    {
        if ($autoPrependFile === null || $autoPrependFile === '') {
            unset($environment['PHP_TEST_CONTEXT_AUTO_PREPEND_FILE']);
            return;
        }

        $environment['PHP_TEST_CONTEXT_AUTO_PREPEND_FILE'] = $autoPrependFile;
    }
}
