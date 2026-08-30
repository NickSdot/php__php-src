--TEST--
Bug #61367: open_basedir bypass in libxml RSHUTDOWN: write test
--EXTENSIONS--
dom
--INI--
open_basedir=.
--FILE--
<?php

class StreamExploiter {
    public $context;

    public function stream_close (  ) {
        $doc = new DOMDocument;
        $doc->appendChild($doc->createTextNode('hello'));
        var_dump($doc->save(dirname(getcwd()) . '/bad'));
    }

    public function stream_open (  $path ,  $mode ,  $options ,  &$opened_path ) {
        return true;
    }
}

$directory = TestPath::directory();
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

Warning: DOMDocument::save(): open_basedir restriction in effect. File(%s) is not within the allowed path(s): (.) in %s on line %d

Warning: DOMDocument::save(): Failed to open stream: %r(Operation not permitted|Insufficient privileges)%r in %s on line %d
bool(false)
