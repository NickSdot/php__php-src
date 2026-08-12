<?php

// TODO: Move address/port info in here?
class CliServerInfo {
    public function __construct(
        public string $docRoot,
        public $processHandle,
    ) {}
}

function php_cli_server_start(
    ?string $code = 'echo "Hello world";',
    ?string $router = 'index.php',
    array $cmd_args = [],
): CliServerInfo {
    $php_executable = getenv('TEST_PHP_EXECUTABLE') ?: PHP_BINARY;

    $suffix = '-' . getmypid() . '-' . bin2hex(random_bytes(8));
    $doc_root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($_SERVER['PHP_SELF'], '.php') . $suffix;

    if (!@mkdir($doc_root, 0700)) {
        throw new RuntimeException(
            "Failed to create CLI server document root: $doc_root",
        );
    }

    if ($code) {
        file_put_contents($doc_root . '/' . ($router ?: 'index.php'), '<?php ' . $code . ' ?>');
    }

    // XXX: This should ideally use the same INI overrides as run-tests.
    $cmd = [$php_executable, '-d', 'error_include_args=0', '-t', $doc_root, '-n', ...$cmd_args, '-S', 'localhost:0'];

    if ($router !== null) {
        $cmd[] = $router;
    }

    $descriptorspec = [
        0 => STDIN,
        1 => STDOUT,
        2 => ['pipe', 'w'],
    ];

    $handle = proc_open($cmd, $descriptorSpec, $pipes, $doc_root, options: ['suppress_errors' => true]);

    if (!is_resource($handle)) {
        remove_directory($doc_root);
        throw new RuntimeException('Failed to create CLI server process');
    }

    register_shutdown_function(
        static function ($handle, string $doc_root): void {
            if (!is_resource($handle)) {
                remove_directory($doc_root);
                return;
            }

            proc_terminate($handle);

            for ($i = 0; $i < 60; $i++) {
                $status = proc_get_status($handle);
                if ($status['running']) {
                    usleep(20000);
                    continue;
                }

                proc_close($handle);
                remove_directory($doc_root);
                return;
            }

            // Do not call proc_close() while the process is still running.
        },
        $handle,
        $doc_root,
    );

    // Wait for the server to report the address assigned to port 0.
    $error_stream = $pipes[2];
    $server_output = '';
    $bound = null;
    $startup_pattern = '@PHP \S* Development Server \(https?://(.*?:\d+)\) started@';

    stream_set_blocking($error_stream, false);

    for ($i = 0; $i < 60; $i++) {
        $status = proc_get_status($handle);
        if (!$status['running']) {
            $error = sprintf("Server failed to start (exit code %d)\n",$status['exitcode']);
            break;
        }

        $read = [$error_stream];
        $write = $except = null;
        $selected = stream_select($read, $write, $except, 0, 50000);

        if ($selected === false) {
            $error = "Failed waiting for server startup\n";
            break;
        }
        if ($selected === 0) {
            continue;
        }

        while (($line = fgets($error_stream)) !== false) {
            $server_output .= $line;

            if (preg_match($startup_pattern, $line, $matches)) {
                $bound = $matches[1];
                fclose($error_stream);
                break 2;
            }
        }
    }

    if ($bound === null) {
        $error ??= "Server did not output startup message\n";
        $server_output .= stream_get_contents($error_stream);

        echo $error;
        printf("Server output:\n%s\n", $server_output);
        exit(1);
    }

    // The startup message may be emitted just before the socket begins accepting connections.
    $error = "Unable to connect to server\n";

    for ($i = 0; $i < 60; $i++) {
        $status = proc_get_status($handle);
        if (!$status['running']) {
            $error = "Server stopped\n";
            break;
        }

        $fp = @fsockopen("tcp://$bound");
        if ($fp !== false) {
            fclose($fp);
            $error = '';
            break;
        }

        usleep(20000);
    }

    if ($error !== '') {
        echo $error;
        exit(1);
    }

    $port = (int) substr($bound, strrpos($bound, ':') + 1);

    define('PHP_CLI_SERVER_HOSTNAME', 'localhost');
    define('PHP_CLI_SERVER_PORT', $port);
    define('PHP_CLI_SERVER_ADDRESS', PHP_CLI_SERVER_HOSTNAME . ':' . PHP_CLI_SERVER_PORT);

    return new CliServerInfo($doc_root, $handle);
}

function php_cli_server_connect() {
    $timeout = 1.0;
    $fp = fsockopen(PHP_CLI_SERVER_HOSTNAME, PHP_CLI_SERVER_PORT, $errno, $errstr, $timeout);
    if (!$fp) {
        die("connect failed");
    }
    return $fp;
}

function remove_directory($dir) {
    if (is_dir($dir) === false) {
        return;
    }
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
    @rmdir($dir);
}

?>
