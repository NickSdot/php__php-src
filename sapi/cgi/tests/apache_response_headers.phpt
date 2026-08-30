--TEST--
apache_response_headers()
--SKIPIF--
<?php
include "skipif.inc";
?>
--FILE--
<?php
include "include.inc";
TestContext::useRelativeWorkspace();

$php = get_cgi_path();
reset_env_vars();

$test_file = TestPath::file("apache_response_headers.test.php")->path;

$code  = '<?php';
$code .= '
header( "X-Robots-Tag : noindex,nofollow,noarchive" );
header( "Content-type: text/html; charset=UTF-8" );
header( "Bad-header" );
header( " : " );
header( ":" );
flush();

var_dump( apache_response_headers() );
?>
';

file_put_contents( $test_file, $code );

passthru( "$php -n -q " . escapeshellarg( $test_file ) );

?>
--EXPECTF--
array(3) {
  ["X-Powered-By"]=>
  string(%d) "PHP/%s"
  ["X-Robots-Tag"]=>
  string(26) "noindex,nofollow,noarchive"
  ["Content-type"]=>
  string(24) "text/html; charset=UTF-8"
}
