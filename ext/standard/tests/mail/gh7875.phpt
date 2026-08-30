--TEST--
GH-7875 (mails are sent even if failure to log throws exception)
--SKIPIF--
<?php
if (PHP_OS_FAMILY !== "Windows") {
    if (!extension_loaded('posix')) die('skip POSIX extension not loaded');
    if (posix_geteuid() == 0) die('skip Cannot run test as root.');
}
?>
--INI--
sendmail_path={MAIL:gh7875.mail.out}
mail.log=gh7875.mail.log
--FILE--
<?php
$logFile = TestPath::touch('gh7875.mail.log');
$outFile = TestPath::file('gh7875.mail.out');
chdir($logFile->directory);
chmod($logFile, 0444);
clearstatcache();
if (is_writable($logFile)) {
    chmod($logFile, 0644);
    die("skip cannot make file read-only");
}

function exception_error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

try {
	mail('recipient@example.com', 'Subject', 'Body', []);
	echo 'Not Reached';
} catch (\Exception $e) {
	echo $e->getMessage(), PHP_EOL;
    var_dump(file_exists($outFile));
}
?>
--EXPECTF--
mail(): Failed to open stream: Permission denied
bool(false)
