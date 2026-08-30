--TEST--
Bug #66338 (Optimization binding of class constants is not safely opcacheable)
--INI--
opcache.enable=0
--EXTENSIONS--
opcache
--CONFLICTS--
server
--FILE--
<?php
$officials = TestPath::file();
$clientUS = TestPath::file('clientUS.php');
$clientUK = TestPath::file('clientUK.php');
$clientUSUrl = str_replace(__DIR__ . '/', '', $clientUS->path);
$clientUKUrl = str_replace(__DIR__ . '/', '', $clientUK->path);

file_put_contents($officials, '<?php
    class Officials { static function getLeader() { return LocalTerms::GOV_LEADER; } }
    ' );

file_put_contents($clientUS, '<?php
    class LocalTerms { const GOV_LEADER = "Barack Hussein Obama II"; }
    require \'' . $officials . '\';
    printf( "The President of the USA is %s\n", Officials::getLeader() );
    ' );

file_put_contents($clientUK, '<?php
    class LocalTerms { const GOV_LEADER = "David William Donald Cameron"; }
    require \'' . $officials . '\';
    printf( "The Prime Minister of the UK is %s\n", Officials::getLeader() );
    ' );

include "php_cli_server.inc";
$opt = -1;   # This test works if $opt = 0
php_cli_server_start("-d opcache.enable=1 -d opcache.enable_cli=1 -d opcache.optimization_level=$opt -d opcache.file_update_protection=0" );

echo file_get_contents("http://" . PHP_CLI_SERVER_ADDRESS . "/$clientUSUrl" );
echo file_get_contents("http://" . PHP_CLI_SERVER_ADDRESS . "/$clientUKUrl" );
?>
--EXPECT--
The President of the USA is Barack Hussein Obama II
The Prime Minister of the UK is David William Donald Cameron
