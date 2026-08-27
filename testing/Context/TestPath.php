<?php

use TestSuite\Context\Filesystem\Directory;
use TestSuite\Context\Filesystem\File;

require_once __DIR__
    . DIRECTORY_SEPARATOR . 'Filesystem'
    . DIRECTORY_SEPARATOR . 'Path.php';
require_once __DIR__
    . DIRECTORY_SEPARATOR . 'Filesystem'
    . DIRECTORY_SEPARATOR . 'File.php';
require_once __DIR__
    . DIRECTORY_SEPARATOR . 'Filesystem'
    . DIRECTORY_SEPARATOR . 'Directory.php';

final class TestPath
{
    public static function file(?string $name = null): File
    {
        return new File(TestContext::allocatePath($name, 'file'));
    }

    public static function directory(?string $name = null): Directory
    {
        return new Directory(TestContext::allocatePath($name, 'directory'));
    }
}
