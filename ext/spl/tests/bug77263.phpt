--TEST--
Bug #77263 (Segfault when using 2 RecursiveFilterIterator)
--FILE--
<?php
$dir = TestPath::directory()->create();
$subdir = $dir->directory()->create();
$dir->file()->create();
$subdir->file()->create();

class Filter1 extends RecursiveFilterIterator {
    public function accept(): bool { return $this->getInnerIterator()->getSubPathname() != ''; }
}

class Filter2 extends RecursiveFilterIterator {
    public function accept(): bool { return $this->getInnerIterator()->getSubPathname() != ' '; }
}

$iterator = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS );

$iterator = new Filter1( $iterator );

$iterator = new Filter2( $iterator );

$iterator = new RecursiveIteratorIterator( $iterator, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD );

foreach ( $iterator as $item ) {
}
?>
OK
--EXPECT--
OK
