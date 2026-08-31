<?php

use TestSuite\Context\Filesystem\Directory;
use TestSuite\Context\Filesystem\File;
use TestSuite\Context\Ini;
use TestSuite\Context\Share;
use TestSuite\Context\TestContext;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Filesystem' . DIRECTORY_SEPARATOR . 'Path.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Filesystem' . DIRECTORY_SEPARATOR . 'File.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Filesystem' . DIRECTORY_SEPARATOR . 'Directory.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestContext.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Ini.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Share.php';

final class Workspace
{
    public static function path(): string
    {
        return TestContext::instance()->workspaceDirectory();
    }

    public static function file(?string $name = null): File
    {
        return new File(TestContext::instance()->allocatePath($name, 'file'));
    }

    public static function directory(?string $name = null): Directory
    {
        return new Directory(TestContext::instance()->allocatePath($name, 'directory'));
    }

    public static function useRelative(): void
    {
        TestContext::instance()->useRelativeWorkspace();
    }

    public static function ini(array|string $settings): void
    {
        Ini::put($settings);
    }

    public static function share(string|array $variables): void
    {
        Share::put($variables);
    }
}
