--TEST--
GH-16229 (Address overflowed in ext/mbstring/mbstring.c:4613 #16229)
--EXTENSIONS--
mbstring
--INI--
sendmail_path={MAIL:mail.eml}
mail.add_x_header=off
--SKIPIF--
<?php
if (!function_exists("mb_send_mail") || !mb_language("japanese")) {
    die("skip mb_send_mail() not available");
}
?>
--FILE--
<?php
$mail = Workspace::file('mail.eml');
chdir($mail->directory);
try {
	$a = '';
	mb_send_mail($a,$a,$a,$a,$a);
} catch (Exception $e) {
}
?>
--EXPECTF--
