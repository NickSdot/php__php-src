--TEST--
Bug #69874: Null addtional_headers does not send mail
--INI--
sendmail_path={MAIL:mailBug69874.out}
mail.add_x_header = Off
--FILE--
<?php
echo "*** Testing mail() : send email without additional headers ***\n";

// Initialise all required variables
$to = 'user@company.com';
$subject = 'Test Subject';
$message = 'A Message';

$outFile = Workspace::file('mailBug69874.out');
chdir($outFile->directory);

var_dump( mail($to, $subject, $message) );
echo file_get_contents($outFile);

?>
--EXPECT--
*** Testing mail() : send email without additional headers ***
bool(true)
To: user@company.com
Subject: Test Subject

A Message
