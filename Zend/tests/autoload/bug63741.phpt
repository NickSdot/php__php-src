--TEST--
Bug #63741 (Crash when autoloading from spl)
--FILE--
<?php
$file = TestPath::file();
file_put_contents($file,
<<<'EOT'
<?php
if (isset($autoloading))
{
    class ClassToLoad
    {
        static function func ()
        {
            print "OK!\n";
        }
    }
    return;
}
else
{
    class autoloader
    {
        static function autoload($classname)
        {
            print "autoloading...\n";
            $autoloading = true;
            include __FILE__;
        }
    }

    spl_autoload_register(["autoloader", "autoload"]);

    function start()
    {
        ClassToLoad::func();
    }

    start();
}
?>
EOT
);

include $file;
?>
--EXPECT--
autoloading...
OK!
