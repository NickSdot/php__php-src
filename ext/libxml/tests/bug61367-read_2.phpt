--TEST--
Bug #61367: open_basedir bypass in libxml RSHUTDOWN: read test
--EXTENSIONS--
dom
--SKIPIF--
<?php
if (LIBXML_VERSION < 20912) die('skip For libxml2 >= 2.9.12 only');
if (preg_match('/[^\x00-\x7F]/', __DIR__)) die('skip path contains non-ASCII characters that libxml URI parser rejects');
?>
--INI--
open_basedir=.
--FILE--
<?php
/*
 * Note: Using error_reporting=E_ALL & ~E_NOTICE to suppress "Trying to get property of non-object" notices.
 */
class StreamExploiter {
    public $context;

    public function stream_close (  ) {
        $doc = new DOMDocument;
        $doc->resolveExternals = true;
        $doc->substituteEntities = true;
        $dir = htmlspecialchars(dirname(getcwd()));
        $dir = str_replace('\\', '/', $dir); // fix for windows
        $doc->loadXML( <<<XML
<!DOCTYPE doc [
    <!ENTITY file SYSTEM "file:///$dir/bad">
]>
<doc>&file;</doc>
XML
        );
        print $doc->documentElement->firstChild->nodeValue;
    }

    public function stream_open (  $path ,  $mode ,  $options ,  &$opened_path ) {
        return true;
    }
}

$directory = Workspace::directory();
$base = $directory->directory();
$bad = $directory->file('bad');
var_dump(mkdir($directory));
var_dump(mkdir($base));
var_dump(file_put_contents($bad, 'blah'));
var_dump(chdir($base));

stream_wrapper_register( 'exploit', 'StreamExploiter' );
$s = fopen( 'exploit://', 'r' );

?>
--EXPECTF--
bool(true)
bool(true)
int(4)
bool(true)

%s: DOMDocument::loadXML(): %s

Warning: Attempt to read property "nodeValue" on null in %s on line %d
