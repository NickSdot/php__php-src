--TEST--
CLI server test helper supports simultaneous processes
--FILE--
<?php
$root = __DIR__ . '/php_cli_server_parallel_' . getmypid();
$basename = basename($root);
$helper = dirname(__DIR__, 2) . '/sapi/cli/tests/php_cli_server.inc';
$processes = [];

$code = <<<'PHP'
    <?php
    require getenv('CLI_SERVER_HELPER');

    $root = getenv('CLI_SERVER_ROOT');
    $index = (int) getenv('CLI_SERVER_INDEX');

    php_cli_server_start("echo 'server $index';");
    touch("$root/ready-$index");

    $deadline = microtime(true) + 5;
    while (count(glob("$root/ready-*")) < 4 && microtime(true) < $deadline) {
        usleep(20000);
    }

    echo file_get_contents('http://' . PHP_CLI_SERVER_ADDRESS), "\n";
    PHP;

mkdir($root);

$environment = [
    'PATH' => getenv('PATH'),
    'TEST_PHP_EXECUTABLE' => getenv('TEST_PHP_EXECUTABLE'),
    'CLI_SERVER_HELPER' => $helper,
    'CLI_SERVER_ROOT' => $root,
];

foreach (['SystemRoot', 'TEMP', 'TMPDIR'] as $name) {
    if (($value = getenv($name)) !== false) {
        $environment[$name] = $value;
    }
}

for ($i = 0; $i < 4; $i++) {
    $directory = "$root/$i";
    $script = "$directory/$basename.php";

    mkdir($directory);
    file_put_contents($script, $code);

    $environment['CLI_SERVER_INDEX'] = (string) $i;

    $process = proc_open(
        [getenv('TEST_PHP_EXECUTABLE'), '-n', $script],
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['redirect', 1],
        ],
        $pipes,
        null,
        $environment,
    );

    fclose($pipes[0]);
    $processes[] = [$process, $pipes, $i];
}

foreach ($processes as [$process, $pipes, $i]) {
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        echo $output;
    }

    var_dump($exitCode);
    var_dump($output === "server $i\n");
}
?>
--CLEAN--
<?php
require dirname(__DIR__, 2) . '/sapi/cli/tests/php_cli_server.inc';

foreach (glob(__DIR__ . '/php_cli_server_parallel_*') ?: [] as $root) {
    $basename = basename($root);
    remove_directory($root);

    $pattern = sys_get_temp_dir()
        . DIRECTORY_SEPARATOR
        . $basename
        . '-*';

    foreach (glob($pattern) ?: [] as $docRoot) {
        remove_directory($docRoot);
    }
}
?>
--EXPECT--
int(0)
bool(true)
int(0)
bool(true)
int(0)
bool(true)
int(0)
bool(true)
