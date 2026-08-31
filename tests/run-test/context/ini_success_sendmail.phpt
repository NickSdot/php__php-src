--TEST--
Workspace INI settings may refer to a shared managed file
--SETUP--
<?php

Workspace::share([
    'outFile' => $outFile = Workspace::file('mail.out')
]);

Workspace::ini([
    'sendmail_path' => 'tee ' . escapeshellarg($outFile->path) . ' >/dev/null',
    'mail.add_x_header' => 'Off',
]);

?>
--SKIPIF--
<?php

if (PHP_OS_FAMILY === 'Windows') {
    die('skip Not available on Windows');
}

?>
--FILE--
<?php

var_dump(mail('user@example.com', 'Test Subject', 'A Message'));
echo file_get_contents($outFile);

?>
--EXPECT--
bool(true)
To: user@example.com
Subject: Test Subject

A Message
