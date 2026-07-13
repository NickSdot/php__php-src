#!/usr/bin/env php
<?php

require_once __DIR__ . '/lib/TestStyleRunner.php';

if (!function_exists('token_get_all')) {
    fwrite(
        STDERR,
        "fix-test-style.php requires the tokenizer extension. Run it with a PHP binary that provides token_get_all(), or build PHP with --enable-tokenizer.\n"
    );
    exit(2);
}

function usage(): void
{
    echo "Usage: php scripts/testing/fix-test-style.php [--check|--print] [--only fixer.php] [path ...]\n";
}

$rootDir = dirname(__DIR__, 2);
$check = false;
$print = false;
$only = null;
$paths = [];

for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];
    if ($arg === '--check') {
        $check = true;
        continue;
    }
    if ($arg === '--print') {
        $print = true;
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        usage();
        exit(0);
    }
    if ($arg === '--only') {
        if (!isset($argv[$i + 1])) {
            fwrite(STDERR, "--only requires a fixer filepath\n");
            exit(2);
        }
        $only = $argv[++$i];
        continue;
    }
    if (str_starts_with($arg, '--only=')) {
        $only = substr($arg, strlen('--only='));
        continue;
    }
    if (str_starts_with($arg, '-')) {
        fwrite(STDERR, "Unknown option: $arg\n");
        usage();
        exit(2);
    }
    $paths[] = $arg;
}

$fixerFiles = $only !== null
    ? [$only]
    : [__DIR__ . '/lib/Fixers/ExceptionMessageEchoFixer.php'];

$fixerClasses = [];
foreach ($fixerFiles as $fixerFile) {
    if (!str_starts_with($fixerFile, DIRECTORY_SEPARATOR)) {
        $fixerFile = $rootDir . DIRECTORY_SEPARATOR . $fixerFile;
    }
    if (!is_file($fixerFile)) {
        fwrite(STDERR, "Fixer file does not exist: $fixerFile\n");
        exit(2);
    }

    $before = get_declared_classes();
    require_once $fixerFile;
    $after = get_declared_classes();
    $newClasses = array_values(array_diff($after, $before));
    foreach ($newClasses as $class) {
        $reflection = new ReflectionClass($class);
        if (!$reflection->isAbstract() && $reflection->isSubclassOf(TestStyleFixer::class)) {
            $fixerClasses[] = $class;
        }
    }
}

if ($fixerClasses === []) {
    fwrite(STDERR, "No TestStyleFixer implementation found\n");
    exit(2);
}
if ($check && $print) {
    fwrite(STDERR, "--check and --print cannot be used together\n");
    exit(2);
}

try {
    $runner = new TestStyleRunner($rootDir, $fixerClasses);
    $files = $runner->collectFiles($paths);
    if ($print) {
        $result = $runner->print($files);
    } else {
        $result = $runner->run($files, $check);
    }
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(2);
}

if ($print) {
    if ($result['failed']) {
        fwrite(STDERR, ($result['failure'] ?? 'unknown reason') . "\n");
        exit(1);
    }
    echo $result['output'];
    exit(0);
}
if ($check && $result['changed'] > 0) {
    exit(1);
}
if ($result['failed'] > 0) {
    exit(1);
}
exit(0);
