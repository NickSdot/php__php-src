--TEST--
revalidate_path 01: OPCache must cache only resolved real paths when revalidate_path is set
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.revalidate_path=1
--EXTENSIONS--
opcache
--CONFLICTS--
server
--FILE--
<?php
TestContext::useRelativeWorkspace();
$workspace = TestPath::directory()->create();
$dir1 = $workspace->directory('test1');
$dir2 = $workspace->directory('test2');
$link = $workspace->directory('test');
$file1 = $dir1->file('index.php');
$file2 = $dir2->file('index.php');
$main = $workspace->file('main.php');
$mainUrl = str_replace(__DIR__ . '/', '', $main->path);
mkdir($dir1);
mkdir($dir2);
file_put_contents($main, '<?php include(\'' . $link .'/index.php\');');
file_put_contents($file1, "TEST 1\n");
file_put_contents($file2, "TEST 2\n");
while (filemtime($file1) != filemtime($file2)) {
    touch($file1);
    touch($file2);
}
if (substr(PHP_OS, 0, 3) == 'WIN') {
    @rmdir($link);
    $ln = str_replace('/', '\\', $link);
    $d1 = realpath($dir1);
    shell_exec("mklink /j $ln $d1");
} else {
    @unlink($link);
    @symlink($dir1, $link);
}

include "php_cli_server.inc";
//php_cli_server_start('-d opcache.enable=1 -d opcache.enable_cli=1 -d opcache.revalidate_path=1');
php_cli_server_start('-d opcache.enable=1 -d opcache.enable_cli=1 -d opcache.revalidate_path=1 -d opcache.file_update_protection=0 -d realpath_cache_size=0');
echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $mainUrl);
echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $mainUrl);
if (substr(PHP_OS, 0, 3) == 'WIN') {
    @rmdir($link);
    $ln = str_replace('/', '\\', $link);
    $d2 = realpath($dir2);
    shell_exec("mklink /j $ln $d2");
} else {
    @unlink($link);
    @symlink($dir2, $link);
}
echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $mainUrl);
echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS . '/' . $mainUrl);
?>
--EXPECT--
TEST 1
TEST 1
TEST 2
TEST 2
