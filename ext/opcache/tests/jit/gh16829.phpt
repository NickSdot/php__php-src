--TEST--
GH-16829 (Segmentation fault with opcache.jit=tracing enabled on aarch64)
--INI--
opcache.jit_buffer_size=32M
--EXTENSIONS--
opcache
--FILE--
<?php
$fixtureDir = TestPath::directory()->create();
$fixture = $fixtureDir->file('gh16829_1.inc');
$dependency = $fixtureDir->file('gh16829_2.inc');
file_put_contents($fixture, file_get_contents(__DIR__ . '/gh16829_1.inc'));
file_put_contents($dependency, file_get_contents(__DIR__ . '/gh16829_2.inc'));
touch($fixture);
require_once($fixture);
?>
DONE
--EXPECT--
DONE
