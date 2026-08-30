--TEST--
Bug GH-8846: Delayed early binding can be used for classes without parents
--EXTENSIONS--
opcache
--CONFLICTS--
server
--INI--
opcache.validate_timestamps=1
opcache.revalidate_freq=0
--FILE--
<?php

$index = TestPath::file('gh8846-index.php');
$indexUrl = str_replace(__DIR__ . '/', '', $index->path);
$script = <<<'PHP'
<?php
if (!@$_GET['skip']) {
    include '__FIXTURE_1__';
}
include '__FIXTURE_2__';
    echo "Ok\n";
PHP;
$script = str_replace(
    ['__FIXTURE_1__', '__FIXTURE_2__'],
    [__DIR__ . '/gh8846-1.inc', __DIR__ . '/gh8846-2.inc'],
    $script,
);
file_put_contents($index, $script);

include 'php_cli_server.inc';
php_cli_server_start('-d opcache.enable=1 -d opcache.enable_cli=1');

echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $indexUrl);
echo "\n";
echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $indexUrl . '?skip=1');
?>
--EXPECTF--
bool(true)
<br />
<b>Fatal error</b>:  Cannot redeclare class Foo (previously declared in %sgh8846-1.inc:2) in <b>%sgh8846-2.inc</b> on line <b>%d</b><br />
Stack trace:
#0 %sgh8846-index.php(%d): include()
#1 {main}

bool(true)
Ok
