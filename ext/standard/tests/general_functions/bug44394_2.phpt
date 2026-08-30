--TEST--
Bug #44394 (Last two bytes missing from output) with session.use_trans_id
--EXTENSIONS--
session
--INI--
session.name=PHPSESSID
session.use_only_cookies=0
session.use_trans_sid=1
session.trans_sid_tags="a=href,area=href,frame=src,form="
url_rewriter.tags="a=href,area=href,frame=src,form="
--FILE--
<?php

$session_dir = TestPath::directory();
mkdir($session_dir);
session_save_path($session_dir);
session_start();

ob_start();

$string = "<a href='a?q=1'>asd</a>";

output_add_rewrite_var('a', 'b');

echo $string;

ob_flush();

ob_end_clean();

?>
--EXPECTF--
Deprecated: PHP Startup: Disabling session.use_only_cookies INI setting is deprecated in Unknown on line 0

Deprecated: PHP Startup: Enabling session.use_trans_sid INI setting is deprecated in Unknown on line 0
<a href='a?q=1&a=b&PHPSESSID=%s'>asd</a>
