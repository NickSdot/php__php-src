--TEST--
GH-20469: Child delayed variance can resolve parent before direct delayed resolution
--DESCRIPTION--
This variant ensures the cacheability check after load_delayed_classes() is
needed. Loading the delayed child resolves the parent class's variance
obligations reentrantly, so the parent no longer has ZEND_ACC_UNRESOLVED_VARIANCE
when control returns from load_delayed_classes(). The parent was still used while
nearly linked, and must not be inserted into the inheritance cache.
--EXTENSIONS--
opcache
--CONFLICTS--
server
--FILE--
<?php
Workspace::useRelative();
$dir = Workspace::directory()->create();
$classes = $dir->directory('classes')->create();
$autoload = $dir->file('autoload.php');
$test1 = $dir->file('test1.php');
$test2 = $dir->file('test2.php');
$test3 = $dir->file('test3.php');
$root = $classes->file('RootForTraitReturn.php');
$parent = $classes->file('ParentBeingLinked.php');
$child = $classes->file('ChildOfParentBeingLinked.php');
$base = $classes->file('BaseCovariantReturn.php');
$requires = $classes->file('RequiresRootReturnTrait.php');
$covariant = $classes->file('CovariantReturnWithTrait.php');
$specific = $classes->file('MoreSpecificReturn.php');
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
 * ParentBeingLinked -> CovariantReturnWithTrait -> RequiresRootReturnTrait
 * -> ChildOfParentBeingLinked -> ParentBeingLinked.
 *
 * ChildOfParentBeingLinked also has delayed variance, so resolving the child's
 * dependency on ParentBeingLinked can resolve ParentBeingLinked before it
 * reaches its direct resolve_delayed_variance_obligations() call.
 */
file_put_contents($test1, <<<'PHP'
<?php
require __DIR__ . '/autoload.php';
// Prime the inheritance cache with the full dependency cycle.
echo \APP\ChildOfParentBeingLinked::SOME_CONSTANT;
PHP);

file_put_contents($test2, <<<'PHP'
<?php
require __DIR__ . '/autoload.php';
// Link ParentBeingLinked first. During load_delayed_classes(), loading the
// delayed child resolves ParentBeingLinked's variance obligations reentrantly.
echo \APP\ParentBeingLinked::SOME_CONSTANT;
$i = new \APP\ChildOfParentBeingLinked();
var_dump($i->test());
PHP);

file_put_contents($test3, <<<'PHP'
<?php
require __DIR__ . '/autoload.php';
// Replay the cache state created by test2. If ParentBeingLinked was cached even
// though it was used while nearly linked, this request fails before test() runs.
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

    // CovariantReturnWithTrait is unavailable when this method is checked,
    // putting ParentBeingLinked into delayed variance resolution.
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
    // MoreSpecificReturn is also unavailable when the child is linked. Resolving
    // this child's delayed variance obligation recursively resolves the parent.
    function createResult(): MoreSpecificReturn
    {
    }
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

    // This pulls ChildOfParentBeingLinked into the delayed autoload queue while
    // ParentBeingLinked is nearly linked.
    function build(): ChildOfParentBeingLinked
    {
    }
}
PHP);

file_put_contents($specific, <<<'PHP'
<?php
namespace APP;

class MoreSpecificReturn extends CovariantReturnWithTrait
{
}
PHP);

include 'php_cli_server.inc';
$ini = trim((string) getenv('TEST_PHP_EXTRA_ARGS'));
$ini .= ($ini !== '' ? ' ' : '') . '-d opcache.enable=1 -d opcache.enable_cli=1 -d opcache.file_update_protection=0';
php_cli_server_start($ini);

echo rtrim(file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $dirUrl . '/test1.php'), "\n"), "\n";
echo rtrim(file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $dirUrl . '/test2.php'), "\n"), "\n";
echo rtrim(file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $dirUrl . '/test3.php'), "\n"), "\n";
?>
--EXPECT--
3
3NULL
3NULL
