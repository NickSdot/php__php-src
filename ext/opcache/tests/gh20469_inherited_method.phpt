--TEST--
GH-20469: Inheritance cache with reentrant autoloading must preserve inherited methods
--EXTENSIONS--
opcache
--CONFLICTS--
server
--FILE--
<?php
TestContext::useRelativeWorkspace();
$dir = TestPath::directory();
mkdir($dir);
$classes = $dir->directory('classes');
mkdir($classes);
$autoload = $dir->file('autoload.php');
$test1 = $dir->file('test1.php');
$test2 = $dir->file('test2.php');
$root = $classes->file('RootForTraitReturn.php');
$parent = $classes->file('ParentBeingLinked.php');
$child = $classes->file('ChildOfParentBeingLinked.php');
$base = $classes->file('BaseCovariantReturn.php');
$requires = $classes->file('RequiresRootReturnTrait.php');
$covariant = $classes->file('CovariantReturnWithTrait.php');
$dirUrl = str_replace(__DIR__ . '/', '', $dir->path);

file_put_contents($autoload, <<<'PHP'
<?php
spl_autoload_register(function ($class) {
    $prefix = 'APP\\';
    if (strncmp($class, $prefix, strlen($prefix)) === 0) {
        require __DIR__ . '/classes/' . substr($class, strlen($prefix)) . '.php';
    }
});
PHP);

/* The dependency cycle is:
 * ChildOfParentBeingLinked -> ParentBeingLinked -> CovariantReturnWithTrait
 * -> RequiresRootReturnTrait -> ChildOfParentBeingLinked.
 */
file_put_contents($test1, <<<'PHP'
<?php
require __DIR__ . '/autoload.php';
echo \APP\ChildOfParentBeingLinked::SOME_CONSTANT;
PHP);

file_put_contents($test2, <<<'PHP'
<?php
require __DIR__ . '/autoload.php';
echo \APP\ParentBeingLinked::SOME_CONSTANT;
$i = new \APP\ChildOfParentBeingLinked();
var_dump($i->test());
PHP);

file_put_contents($root, <<<'PHP'
<?php
namespace APP;

class RootForTraitReturn
{
    function createResult(): BaseCovariantReturn
    {
    }

    function test() {}
}
PHP);

file_put_contents($parent, <<<'PHP'
<?php
namespace APP;

class ParentBeingLinked extends RootForTraitReturn
{
    public const SOME_CONSTANT = 3;

    function createResult(): CovariantReturnWithTrait
    {
    }
}
PHP);

file_put_contents($child, <<<'PHP'
<?php
namespace APP;

class ChildOfParentBeingLinked extends ParentBeingLinked
{
}
PHP);

file_put_contents($base, <<<'PHP'
<?php
namespace APP;

abstract class BaseCovariantReturn
{
}
PHP);

file_put_contents($requires, <<<'PHP'
<?php
namespace APP;

trait RequiresRootReturnTrait
{
    abstract function build(): RootForTraitReturn;
}
PHP);

file_put_contents($covariant, <<<'PHP'
<?php
namespace APP;

class CovariantReturnWithTrait extends BaseCovariantReturn
{
    use RequiresRootReturnTrait;

    function build(): ChildOfParentBeingLinked
    {
    }
}
PHP);

include 'php_cli_server.inc';
$ini = trim((string) getenv('TEST_PHP_EXTRA_ARGS'));
$ini .= ($ini !== '' ? ' ' : '') . '-d opcache.enable=1 -d opcache.enable_cli=1 -d opcache.file_update_protection=0';
php_cli_server_start($ini);

echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $dirUrl . '/test1.php'), "\n";
echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $dirUrl . '/test2.php'), "\n";
?>
--EXPECT--
3
3NULL
