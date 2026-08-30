--TEST--
GHSA-3qgc-jrrr-25jv
--SKIPIF--
<?php
include 'skipif.inc';
if (PHP_OS_FAMILY !== "Windows") die("skip Only for Windows");

$codepage = trim(shell_exec("powershell Get-ItemPropertyValue HKLM:\\SYSTEM\\CurrentControlSet\\Control\\Nls\\CodePage ACP"));
if ($codepage !== '932' && $codepage !== '936' && $codepage !== '950') die("skip Wrong codepage");
?>
--FILE--
<?php
include 'include.inc';
TestContext::useRelativeWorkspace();

$filename = TestPath::file("GHSA-3qgc-jrrr-25jv_tmp.php")->path;
$script = '<?php echo "hello "; echo "world"; ?>';
file_put_contents($filename, $script);

$php = get_cgi_path();
reset_env_vars();

putenv("SERVER_NAME=Test");
putenv("SCRIPT_FILENAME=$filename");
putenv("QUERY_STRING=%ads");
putenv("REDIRECT_STATUS=1");

passthru("$php -s");

?>
--EXPECTF--
X-Powered-By: PHP/%s
Content-type: %s

hello world
