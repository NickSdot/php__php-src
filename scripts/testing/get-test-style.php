#!/usr/bin/env php
<?php

require_once __DIR__ . '/lib/PhptFile.php';
require_once __DIR__ . '/lib/TestStyleRunner.php';
require_once __DIR__ . '/lib/Fixers/ExceptionMessageEchoFixer.php';

if (!function_exists('token_get_all')) {
    fwrite(STDERR, "scripts/testing/get-test-style.php requires the tokenizer extension\n");
    exit(2);
}

function usage(): void
{
    echo "Usage: php scripts/testing/get-test-style.php [--fixtures-dir dir] [--output-dir dir] [path ...]\n";
}

$rootDir = dirname(__DIR__, 2);
$generatedRoot = __DIR__ . '/tests/exception_output_styles';
$fixturesDir = $generatedRoot;
$outputDir = $generatedRoot;
$paths = [];

for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];
    if ($arg === '--help' || $arg === '-h') {
        usage();
        exit(0);
    }
    if ($arg === '--fixtures-dir') {
        if (!isset($argv[$i + 1])) {
            fwrite(STDERR, "--fixtures-dir requires a directory path\n");
            exit(2);
        }
        $fixturesDir = $argv[++$i];
        continue;
    }
    if (str_starts_with($arg, '--fixtures-dir=')) {
        $fixturesDir = substr($arg, strlen('--fixtures-dir='));
        continue;
    }
    if ($arg === '--output-dir') {
        if (!isset($argv[$i + 1])) {
            fwrite(STDERR, "--output-dir requires a directory path\n");
            exit(2);
        }
        $outputDir = $argv[++$i];
        continue;
    }
    if (str_starts_with($arg, '--output-dir=')) {
        $outputDir = substr($arg, strlen('--output-dir='));
        continue;
    }
    if (str_starts_with($arg, '-')) {
        fwrite(STDERR, "Unknown option: $arg\n");
        usage();
        exit(2);
    }
    $paths[] = $arg;
}

$fixturesDir = absolutePath($fixturesDir, $rootDir);
$outputDir = absolutePath($outputDir, $rootDir);
$paths = $paths === [] ? [$rootDir] : $paths;

try {
    $generatedRoot = realpath($generatedRoot) ?: $generatedRoot;
    $collected = collectExceptionMessageStyles($paths, $rootDir, $generatedRoot);
    $echoVariants = $collected['echoVariants'];
    $lineVariants = $collected['lineVariants'];
    $phptFileCount = $collected['phptFileCount'];
    if ($echoVariants === []) {
        fwrite(STDERR, "No exception message echo variants found\n");
        exit(1);
    }

    $fixtureStats = writeFixturePairs($echoVariants, $fixturesDir, $rootDir);
    $coverage = buildCoverage($lineVariants, $echoVariants);
    writeCoverageFiles($coverage, $outputDir, $phptFileCount, $fixtureStats);

    echo "Scanned $phptFileCount PHPT files\n";
    echo "Generated {$fixtureStats['pairs']} source-copy fixture pairs in $fixturesDir\n";
    echo "Verified {$fixtureStats['verified']} rewritten fixture pairs with run-tests.php\n";
    echo 'Wrote ' . count($lineVariants) . " getMessage line classifications to $outputDir\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(2);
}

function absolutePath(string $path, string $rootDir): string
{
    if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
        return $path;
    }

    return $rootDir . DIRECTORY_SEPARATOR . $path;
}

/**
 * @param list<string> $paths
 * @return array{echoVariants: list<array{key: string, statement: string, catchVar: string, source: string, sourcePath: string, line: int, fixtureOld?: string, fixtureNew?: string, fixtureDiff?: string, fixtureVerified?: bool, fixtureFailure?: string|null, rewritten?: bool}>, lineVariants: list<array{key: string, variant: string, source: string, sourcePath: string, line: int, statement: string|null, catchVar: string|null, expectedOutput: string|null}>, phptFileCount: int}
 */
function collectExceptionMessageStyles(array $paths, string $rootDir, string $fixtureRoot): array
{
    $echoVariants = [];
    $lineVariants = [];
    $files = phptFiles($paths, $rootDir, $fixtureRoot);

    foreach ($files as $path) {
        collectFromFile($path, $rootDir, $fixtureRoot, $echoVariants, $lineVariants);
    }

    ksort($echoVariants);
    ksort($lineVariants);

    return [
        'echoVariants' => array_values($echoVariants),
        'lineVariants' => array_values($lineVariants),
        'phptFileCount' => count($files),
    ];
}

/**
 * @param list<string> $paths
 * @return list<string>
 */
function phptFiles(array $paths, string $rootDir, string $fixtureRoot): array
{
    $files = [];

    foreach ($paths as $path) {
        if (!str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $path = $rootDir . DIRECTORY_SEPARATOR . $path;
        }
        if (is_file($path)) {
            $realPath = realpath($path) ?: $path;
            if (str_ends_with($path, '.phpt') && !str_starts_with($realPath, $fixtureRoot . DIRECTORY_SEPARATOR)) {
                $files[] = $path;
            }
            continue;
        }
        if (!is_dir($path)) {
            throw new InvalidArgumentException("Path does not exist: $path");
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), '.phpt')) {
                continue;
            }
            $realPath = realpath($file->getPathname()) ?: $file->getPathname();
            if (!str_starts_with($realPath, $fixtureRoot . DIRECTORY_SEPARATOR)) {
                $files[] = $file->getPathname();
            }
        }
    }

    sort($files);
    return array_values(array_unique($files));
}

/**
 * @param array<string, array{key: string, statement: string, catchVar: string, source: string, sourcePath: string, line: int}> $echoVariants
 * @param array<string, array{key: string, variant: string, source: string, sourcePath: string, line: int, statement: string|null, catchVar: string|null, expectedOutput: string|null}> $lineVariants
 */
function collectFromFile(string $path, string $rootDir, string $fixtureRoot, array &$echoVariants, array &$lineVariants): void
{
    $realPath = realpath($path) ?: $path;
    if (str_starts_with($realPath, $fixtureRoot . DIRECTORY_SEPARATOR)) {
        return;
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        return;
    }

    $section = codeSection($contents);
    if ($section === null) {
        return;
    }
    $expected = expectedSection($contents);

    $tokens = tokenizePhp($section['code']);
    $echoStatements = collectEchoStatements($section['code'], $tokens);
    $relativePath = str_starts_with($path, $rootDir . DIRECTORY_SEPARATOR) ? substr($path, strlen($rootDir) + 1) : $path;

    foreach ($echoStatements as $statement) {
        if (!tokensContainGetMessage($tokens, $statement['start'], $statement['end'])) {
            continue;
        }

        $key = normalizeCode($statement['text']);
        if (isset($echoVariants[$key])) {
            continue;
        }

        $echoVariants[$key] = [
            'key' => $key,
            'statement' => $statement['text'],
            'catchVar' => catchVariableForStatement($statement['text']),
            'source' => $relativePath,
            'sourcePath' => $realPath,
            'line' => $section['startLine'] + $tokens[$statement['start']]['line'] - 1,
        ];
    }

    $sourceLines = preg_split('/\R/', $section['code']);
    foreach (getMessageTokenIndexes($tokens) as $tokenIndex) {
        $line = $tokens[$tokenIndex]['line'];
        $codeLine = trim($sourceLines[$line - 1] ?? '');
        if ($codeLine === '') {
            continue;
        }

        $statement = echoStatementContaining($echoStatements, $tokenIndex);
        $statementText = $statement === null ? null : $statement['text'];
        $variant = $statementText ?? $codeLine;
        $key = normalizeCode($variant);
        if (isset($lineVariants[$key])) {
            continue;
        }

        $lineVariants[$key] = [
            'key' => $key,
            'variant' => $variant,
            'source' => $relativePath,
            'sourcePath' => $realPath,
            'line' => $section['startLine'] + $line - 1,
            'statement' => $statementText,
            'catchVar' => $statementText === null ? null : '$' . catchVariableForStatement($statementText),
            'expectedOutput' => $expected['output'] ?? null,
        ];
    }
}

/**
 * @return array{code: string, startLine: int}|null
 */
function codeSection(string $contents): ?array
{
    if (!preg_match_all('/^--([_A-Z]+)--[ \t]*(?:\r\n|\n|\r|$)/m', $contents, $matches, PREG_OFFSET_CAPTURE)) {
        return null;
    }

    $count = count($matches[0]);
    for ($i = 0; $i < $count; $i++) {
        $name = $matches[1][$i][0];
        if ($name !== 'FILE' && $name !== 'FILEEOF') {
            continue;
        }

        $contentStart = $matches[0][$i][1] + strlen($matches[0][$i][0]);
        $contentEnd = $i + 1 < $count ? $matches[0][$i + 1][1] : strlen($contents);

        return [
            'code' => substr($contents, $contentStart, $contentEnd - $contentStart),
            'startLine' => substr_count(substr($contents, 0, $contentStart), "\n") + 1,
        ];
    }

    return null;
}

/**
 * @return array{name: string, output: string}|null
 */
function expectedSection(string $contents): ?array
{
    if (!preg_match_all('/^--([_A-Z]+)--[ \t]*(?:\r\n|\n|\r|$)/m', $contents, $matches, PREG_OFFSET_CAPTURE)) {
        return null;
    }

    $count = count($matches[0]);
    for ($i = 0; $i < $count; $i++) {
        $name = $matches[1][$i][0];
        if (!in_array($name, ['EXPECT', 'EXPECTF', 'EXPECTREGEX'], true)) {
            continue;
        }

        $contentStart = $matches[0][$i][1] + strlen($matches[0][$i][0]);
        $contentEnd = $i + 1 < $count ? $matches[0][$i + 1][1] : strlen($contents);

        return [
            'name' => $name,
            'output' => substr($contents, $contentStart, $contentEnd - $contentStart),
        ];
    }

    return null;
}

/**
 * @return list<array{id: int|null, text: string, offset: int, line: int}>
 */
function tokenizePhp(string $code): array
{
    $tokens = [];
    $offset = 0;
    $line = 1;

    foreach (@token_get_all($code) as $token) {
        if (is_array($token)) {
            $text = $token[1];
            $tokens[] = [
                'id' => $token[0],
                'text' => $text,
                'offset' => $offset,
                'line' => $line,
            ];
        } else {
            $text = $token;
            $tokens[] = [
                'id' => null,
                'text' => $text,
                'offset' => $offset,
                'line' => $line,
            ];
        }

        $offset += strlen($text);
        $line += substr_count($text, "\n");
    }

    return $tokens;
}

/**
 * @param list<array{id: int|null, text: string, offset: int, line: int}> $tokens
 * @return list<array{start: int, end: int, text: string}>
 */
function collectEchoStatements(string $code, array $tokens): array
{
    $statements = [];
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        if (!isOutputStatementStart($tokens, $i)) {
            continue;
        }

        $end = findStatementEnd($tokens, $i);
        if ($end === null) {
            continue;
        }

        $startOffset = $tokens[$i]['offset'];
        $endOffset = $tokens[$end]['offset'] + strlen($tokens[$end]['text']);
        $statements[] = [
            'start' => $i,
            'end' => $end,
            'text' => trim(substr($code, $startOffset, $endOffset - $startOffset)),
        ];
        $i = $end;
    }

    return $statements;
}

/**
 * @param list<array{id: int|null, text: string, offset: int, line: int}> $tokens
 */
function isOutputStatementStart(array $tokens, int $index): bool
{
    $id = $tokens[$index]['id'] ?? null;
    if (in_array($id, [T_ECHO, T_PRINT], true)) {
        return true;
    }

    return $id === T_STRING && strtolower($tokens[$index]['text']) === 'var_dump';
}

/**
 * @param list<array{id: int|null, text: string, offset: int, line: int}> $tokens
 */
function findStatementEnd(array $tokens, int $echoIndex): ?int
{
    $paren = 0;
    $bracket = 0;
    $brace = 0;
    $count = count($tokens);

    for ($i = $echoIndex + 1; $i < $count; $i++) {
        $id = $tokens[$i]['id'] ?? null;
        if ($id !== null && $id !== T_CURLY_OPEN) {
            continue;
        }

        $text = $tokens[$i]['text'];
        if ($text === '(') {
            $paren++;
            continue;
        }
        if ($text === ')') {
            $paren--;
            continue;
        }
        if ($text === '[') {
            $bracket++;
            continue;
        }
        if ($text === ']') {
            $bracket--;
            continue;
        }
        if ($text === '{') {
            $brace++;
            continue;
        }
        if ($text === '}') {
            if ($brace === 0) {
                return null;
            }
            $brace--;
            continue;
        }
        if ($text === ';' && $paren === 0 && $bracket === 0 && $brace === 0) {
            return $i;
        }
    }

    return null;
}

/**
 * @param list<array{id: int|null, text: string, offset: int, line: int}> $tokens
 */
function tokensContainGetMessage(array $tokens, int $start, int $end): bool
{
    foreach (getMessageTokenIndexes($tokens, $start, $end) as $_) {
        return true;
    }

    return false;
}

/**
 * @param list<array{id: int|null, text: string, offset: int, line: int}> $tokens
 * @return list<int>
 */
function getMessageTokenIndexes(array $tokens, int $start = 0, ?int $end = null): array
{
    $indexes = [];
    $end ??= count($tokens) - 1;

    for ($i = $start; $i <= $end; $i++) {
        if (($tokens[$i]['id'] ?? null) !== T_OBJECT_OPERATOR) {
            continue;
        }

        $name = nextSignificant($tokens, $i + 1, $end + 1);
        if ($name === null || strtolower($tokens[$name]['text']) !== 'getmessage') {
            continue;
        }

        $openParen = nextSignificant($tokens, $name + 1, $end + 1);
        if ($openParen !== null && $tokens[$openParen]['text'] === '(') {
            $indexes[] = $name;
        }
    }

    return $indexes;
}

/**
 * @param list<array{start: int, end: int, text: string}> $echoStatements
 * @return array{start: int, end: int, text: string}|null
 */
function echoStatementContaining(array $echoStatements, int $tokenIndex): ?array
{
    foreach ($echoStatements as $statement) {
        if ($tokenIndex >= $statement['start'] && $tokenIndex <= $statement['end']) {
            return $statement;
        }
    }

    return null;
}

/**
 * @param list<array{id: int|null, text: string, offset: int, line: int}> $tokens
 */
function nextSignificant(array $tokens, int $start, ?int $end = null): ?int
{
    $end ??= count($tokens);
    for ($i = $start; $i < $end; $i++) {
        if (!isIgnorable($tokens[$i])) {
            return $i;
        }
    }

    return null;
}

/**
 * @param array{id: int|null, text: string, offset: int, line: int} $token
 */
function isIgnorable(array $token): bool
{
    return in_array($token['id'], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true);
}

function normalizeCode(string $code): string
{
    $normalized = '';
    foreach (@token_get_all('<?php ' . $code) as $token) {
        if (is_array($token)) {
            if (in_array($token[0], [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            $normalized .= $token[1];
            continue;
        }

        $normalized .= $token;
    }

    return trim($normalized);
}

function catchVariableForStatement(string $statement): string
{
    $normalized = normalizeCode($statement);
    if (preg_match('~\$([A-Za-z_][A-Za-z0-9_]*)->getMessage\s*\(~', $normalized, $matches)) {
        return $matches[1];
    }
    if (preg_match('~\$([A-Za-z_][A-Za-z0-9_]*)->~', $normalized, $matches)) {
        return $matches[1];
    }

    return 'e';
}

/**
 * @param list<array{key: string, statement: string, catchVar: string, source: string, sourcePath: string, line: int, fixtureOld?: string, fixtureNew?: string, fixtureDiff?: string, fixtureVerified?: bool, fixtureFailure?: string|null, rewritten?: bool}> $echoVariants
 * @return array{pairs: int, verified: int, unchanged: int, failed: int}
 */
function writeFixturePairs(array &$echoVariants, string $fixturesDir, string $rootDir): array
{
    prepareGeneratedDirectory($fixturesDir);
    if (!is_dir($fixturesDir) && !mkdir($fixturesDir, 0777, true)) {
        throw new RuntimeException("Cannot create $fixturesDir");
    }

    $fixturesBySource = [];
    foreach ($echoVariants as $variant) {
        $fixturesBySource[$variant['source']] = [
            'source' => $variant['source'],
            'sourcePath' => $variant['sourcePath'],
            'fixtureName' => fixtureDirectoryName($variant['source']),
        ];
    }
    ksort($fixturesBySource);

    $usedFixtureNames = [];
    $fixtureStats = [
        'pairs' => 0,
        'verified' => 0,
        'unchanged' => 0,
        'failed' => 0,
    ];
    foreach ($fixturesBySource as $fixture) {
        $fixtureName = $fixture['fixtureName'];
        if (isset($usedFixtureNames[$fixtureName])) {
            throw new RuntimeException(
                "Fixture filename collision for {$fixture['source']} and {$usedFixtureNames[$fixtureName]}: $fixtureName"
            );
        }
        $usedFixtureNames[$fixtureName] = $fixture['source'];

        $fixtureDir = $fixturesDir . DIRECTORY_SEPARATOR . $fixtureName;
        if (!mkdir($fixtureDir, 0777, true) && !is_dir($fixtureDir)) {
            throw new RuntimeException("Cannot create $fixtureDir");
        }

        $oldPath = $fixtureDir . DIRECTORY_SEPARATOR . 'old.phpt';
        $newPath = $fixtureDir . DIRECTORY_SEPARATOR . 'new.phpt';
        $diffPath = $fixtureDir . DIRECTORY_SEPARATOR . 'ran.diff';

        if (!copy($fixture['sourcePath'], $oldPath)) {
            throw new RuntimeException("Cannot copy {$fixture['sourcePath']} to $oldPath");
        }

        $rewrite = rewriteFixtureTarget($oldPath, $newPath, $rootDir);
        if ($rewrite['failure'] !== null) {
            removeDirectory($fixtureDir);
            $fixturesBySource[$fixture['source']]['fixtureVerified'] = false;
            $fixturesBySource[$fixture['source']]['fixtureFailure'] = $rewrite['failure'];
            $fixturesBySource[$fixture['source']]['newKeysByLine'] = [];
            $fixtureStats['failed']++;
            continue;
        }

        writeTextFile($diffPath, unifiedDiff($oldPath, $newPath, 'old.phpt', 'new.phpt'));

        $newKeysByLine = statementKeysByLine($newPath);
        $fixtureStats['pairs']++;
        $fixturesBySource[$fixture['source']]['fixtureOld'] = relativePath($oldPath, $rootDir);
        $fixturesBySource[$fixture['source']]['fixtureNew'] = relativePath($newPath, $rootDir);
        $fixturesBySource[$fixture['source']]['fixtureDiff'] = relativePath($diffPath, $rootDir);
        $fixturesBySource[$fixture['source']]['fixtureVerified'] = $rewrite['verified'];
        $fixturesBySource[$fixture['source']]['fixtureFailure'] = $rewrite['failure'];
        $fixturesBySource[$fixture['source']]['newKeysByLine'] = $newKeysByLine;

        if ($rewrite['verified']) {
            $fixtureStats['verified']++;
        } elseif ($rewrite['failure'] !== null) {
            $fixtureStats['failed']++;
        } else {
            $fixtureStats['unchanged']++;
        }
    }

    foreach ($echoVariants as &$variant) {
        $fixture = $fixturesBySource[$variant['source']];
        if (isset($fixture['fixtureOld'])) {
            $variant['fixtureOld'] = $fixture['fixtureOld'];
            $variant['fixtureNew'] = $fixture['fixtureNew'];
            $variant['fixtureDiff'] = $fixture['fixtureDiff'];
        }
        $variant['fixtureVerified'] = $fixture['fixtureVerified'];
        $variant['fixtureFailure'] = $fixture['fixtureFailure'];
        $variant['rewritten'] = $fixture['fixtureVerified']
            && !isset($fixture['newKeysByLine'][$variant['line']][$variant['key']]);
    }
    unset($variant);

    return $fixtureStats;
}

function prepareGeneratedDirectory(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    foreach (new DirectoryIterator($dir) as $entry) {
        if ($entry->isDot()) {
            continue;
        }
        if ($entry->isDir()) {
            removeDirectory($entry->getPathname());
            continue;
        }
        if (!unlink($entry->getPathname())) {
            throw new RuntimeException("Cannot remove file {$entry->getPathname()}");
        }
    }
}

function removeDirectory(string $dir): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            if (!rmdir($file->getPathname())) {
                throw new RuntimeException("Cannot remove directory {$file->getPathname()}");
            }
            continue;
        }
        if (!unlink($file->getPathname())) {
            throw new RuntimeException("Cannot remove file {$file->getPathname()}");
        }
    }
    if (!rmdir($dir)) {
        throw new RuntimeException("Cannot remove directory $dir");
    }
}

function fixtureDirectoryName(string $source): string
{
    $base = preg_replace('~\.phpt$~', '', $source);
    $base = preg_replace('~[^A-Za-z0-9]+~', '_', $base);
    $base = trim((string) $base, '_');
    return $base;
}

function relativePath(string $path, string $rootDir): string
{
    if (str_starts_with($path, $rootDir . DIRECTORY_SEPARATOR)) {
        return substr($path, strlen($rootDir) + 1);
    }

    return $path;
}

/**
 * @return array{verified: bool, failure: string|null}
 */
function rewriteFixtureTarget(string $oldPath, string $newPath, string $rootDir): array
{
    $runner = new TestStyleRunner($rootDir, [ExceptionMessageEchoFixer::class]);
    $rewrite = $runner->printFile($oldPath);
    if ($rewrite['failed']) {
        return ['verified' => false, 'failure' => $rewrite['failure']];
    }
    writeTextFile($newPath, $rewrite['output']);

    return ['verified' => $rewrite['changed'], 'failure' => null];
}

/**
 * @return array<int, array<string, true>>
 */
function statementKeysByLine(string $path): array
{
    $contents = file_get_contents($path);
    if ($contents === false) {
        throw new RuntimeException("Cannot read $path");
    }

    $section = codeSection($contents);
    if ($section === null) {
        return [];
    }

    $tokens = tokenizePhp($section['code']);
    $keys = [];
    foreach (collectEchoStatements($section['code'], $tokens) as $statement) {
        if (tokensContainGetMessage($tokens, $statement['start'], $statement['end'])) {
            $line = $section['startLine'] + $tokens[$statement['start']]['line'] - 1;
            $keys[$line][normalizeCode($statement['text'])] = true;
        }
    }

    return $keys;
}

function unifiedDiff(string $oldPath, string $newPath, string $oldLabel, string $newLabel): string
{
    $diff = unifiedDiffFromCommand($oldPath, $newPath, $oldLabel, $newLabel);
    if ($diff !== null) {
        return $diff;
    }

    return unifiedDiffFallback($oldPath, $newPath, $oldLabel, $newLabel);
}

function unifiedDiffFromCommand(string $oldPath, string $newPath, string $oldLabel, string $newLabel): ?string
{
    $command = ['diff', '-u', '--label', $oldLabel, '--label', $newLabel, $oldPath, $newPath];
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = @proc_open($command, $descriptorSpec, $pipes);
    if (!is_resource($process)) {
        return null;
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode === 0 || $exitCode === 1) {
        return (string) $stdout;
    }

    return null;
}

function unifiedDiffFallback(string $oldPath, string $newPath, string $oldLabel, string $newLabel): string
{
    $old = file_get_contents($oldPath);
    $new = file_get_contents($newPath);
    if ($old === false || $new === false) {
        throw new RuntimeException("Cannot read files for diff");
    }

    if ($old === $new) {
        return '';
    }

    $oldLines = splitDiffLines($old);
    $newLines = splitDiffLines($new);
    $lines = [
        "--- $oldLabel\n",
        "+++ $newLabel\n",
        '@@ -1,' . count($oldLines) . ' +1,' . count($newLines) . " @@\n",
    ];

    foreach ($oldLines as $line) {
        $lines[] = '-' . $line;
    }
    foreach ($newLines as $line) {
        $lines[] = '+' . $line;
    }

    return implode('', $lines);
}

/**
 * @return list<string>
 */
function splitDiffLines(string $contents): array
{
    if ($contents === '') {
        return [];
    }

    return preg_split('/(?<=\n)/', $contents, -1, PREG_SPLIT_NO_EMPTY) ?: [];
}

/**
 * @param list<array{key: string, variant: string, source: string, sourcePath: string, line: int, statement: string|null, catchVar: string|null, expectedOutput: string|null}> $lineVariants
 * @param list<array{key: string, statement: string, catchVar: string, source: string, sourcePath: string, line: int, fixtureOld?: string, fixtureNew?: string, fixtureDiff?: string, fixtureVerified?: bool, fixtureFailure?: string|null, rewritten?: bool}> $echoVariants
 * @return array{fixed: list<array{0: array<string, mixed>, 1: array{status: string, reason: string}}>, non_asserts: list<array{0: array<string, mixed>, 1: array{status: string, reason: string}}>, non_fixable: list<array{0: array<string, mixed>, 1: array{status: string, reason: string}}>, reasonCounts: array<string, int>, filesWithGetMessage: array<string, bool>, filesWithNonFixable: array<string, bool>, fixtureByKey: array<string, array<string, mixed>>, variantCount: int}
 */
function buildCoverage(array $lineVariants, array $echoVariants): array
{
    $coverage = [
        'fixed' => [],
        'non_asserts' => [],
        'non_fixable' => [],
        'reasonCounts' => [],
        'filesWithGetMessage' => [],
        'filesWithNonFixable' => [],
        'fixtureByKey' => [],
        'variantCount' => count($lineVariants),
    ];
    foreach ($echoVariants as $echoVariant) {
        $coverage['fixtureByKey'][$echoVariant['key']] = $echoVariant;
    }

    foreach ($lineVariants as $variant) {
        $coverage['filesWithGetMessage'][$variant['source']] = true;
        $classification = classifyGetMessageLine($variant, $coverage['fixtureByKey']);
        $reason = $classification['status'] . ': ' . $classification['reason'];
        $coverage['reasonCounts'][$reason] = ($coverage['reasonCounts'][$reason] ?? 0) + 1;

        if ($classification['status'] === 'covered') {
            $coverage['fixed'][] = [$variant, $classification];
        } elseif ($classification['status'] === 'not-actionable') {
            $coverage['non_asserts'][] = [$variant, $classification];
        } else {
            $coverage['non_fixable'][] = [$variant, $classification];
            $coverage['filesWithNonFixable'][$variant['source']] = true;
        }
    }

    ksort($coverage['reasonCounts']);

    return $coverage;
}

/**
 * @param array{fixed: list<array{0: array<string, mixed>, 1: array{status: string, reason: string}}>, non_asserts: list<array{0: array<string, mixed>, 1: array{status: string, reason: string}}>, non_fixable: list<array{0: array<string, mixed>, 1: array{status: string, reason: string}}>, reasonCounts: array<string, int>, filesWithGetMessage: array<string, bool>, filesWithNonFixable: array<string, bool>, fixtureByKey: array<string, array<string, mixed>>, variantCount: int} $coverage
 */
/**
 * @param array{pairs: int, verified: int, unchanged: int, failed: int} $fixtureStats
 */
function writeCoverageFiles(array $coverage, string $outputDir, int $phptFileCount, array $fixtureStats): void
{
    if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true)) {
        throw new RuntimeException("Cannot create output directory: $outputDir");
    }

    writeTextFile(
        $outputDir . DIRECTORY_SEPARATOR . 'stats.txt',
        renderStats($coverage, $phptFileCount, $fixtureStats)
    );
    writeTextFile(
        $outputDir . DIRECTORY_SEPARATOR . 'fixed.txt',
        renderCoverageEntries('Fixed by current tool or already canonical', $coverage['fixed'], $coverage['fixtureByKey'])
    );
    writeTextFile(
        $outputDir . DIRECTORY_SEPARATOR . 'non_fixable.txt',
        renderCoverageEntries('Not fixable by current tool', $coverage['non_fixable'], $coverage['fixtureByKey'])
    );
    writeTextFile(
        $outputDir . DIRECTORY_SEPARATOR . 'non_asserts.txt',
        renderCoverageEntries('Not exception-output assertions', $coverage['non_asserts'], $coverage['fixtureByKey'])
    );
}

/**
 * @param array{fixed: list<array{0: array<string, mixed>, 1: array{status: string, reason: string}}>, non_asserts: list<array{0: array<string, mixed>, 1: array{status: string, reason: string}}>, non_fixable: list<array{0: array<string, mixed>, 1: array{status: string, reason: string}}>, reasonCounts: array<string, int>, filesWithGetMessage: array<string, bool>, filesWithNonFixable: array<string, bool>, fixtureByKey: array<string, array<string, mixed>>, variantCount: int} $coverage
 * @param array{pairs: int, verified: int, unchanged: int, failed: int} $fixtureStats
 */
function renderStats(array $coverage, int $phptFileCount, array $fixtureStats): string
{
    $variantCount = $coverage['variantCount'];
    $rewriteCount = reasonCount($coverage['reasonCounts'], 'covered: fixer rewrites this output shape');
    $alreadyCanonicalCount = reasonCount($coverage['reasonCounts'], 'covered: already canonical');
    $verificationFailureCount = reasonCount($coverage['reasonCounts'], 'not-covered: rewrite failed verification');
    $nonAssertCount = count($coverage['non_asserts']);
    $nonFixableCount = count($coverage['non_fixable']);
    $unsupportedCount = $nonFixableCount - $verificationFailureCount;
    $fixedCount = count($coverage['fixed']);
    $filesWithGetMessage = count($coverage['filesWithGetMessage']);
    $filesWithNonFixable = count($coverage['filesWithNonFixable']);

    $lines = [
        '# Exception message style statistics',
        '',
        'Generated by scripts/testing/get-test-style.php.',
        'By default this scans every .phpt file under the repository, excluding generated fixtures.',
        'Fixture pairs and reports remain in scripts/testing/tests/exception_output_styles.',
        '',
        '## Files',
        renderTable([
            ['Metric', 'Count', 'Share'],
            ['Scanned PHPT files', (string) $phptFileCount, '100.00%'],
            ['Files containing ->getMessage()', (string) $filesWithGetMessage, percent($filesWithGetMessage, $phptFileCount) . ' of scanned'],
            ['Files with non-fixed getMessage variants', (string) $filesWithNonFixable, percent($filesWithNonFixable, max(1, $filesWithGetMessage)) . ' of ->getMessage files'],
        ]),
        '',
        '## Report Files',
        renderTable([
            ['Output file', 'Entries', 'Share'],
            ['fixed.txt', (string) $fixedCount, percent($fixedCount, $variantCount)],
            ['non_asserts.txt', (string) $nonAssertCount, percent($nonAssertCount, $variantCount)],
            ['non_fixable.txt', (string) $nonFixableCount, percent($nonFixableCount, $variantCount)],
            ['Total', (string) ($fixedCount + $nonAssertCount + $nonFixableCount), percent($fixedCount + $nonAssertCount + $nonFixableCount, $variantCount)],
        ]),
        '',
        '## Variants',
        renderTable([
            ['Bucket', 'Tool action', 'Count', 'Share', 'Output file'],
            ['Fixer can rewrite', 'rewrite', (string) $rewriteCount, percent($rewriteCount, $variantCount), 'fixed.txt'],
            ['Already canonical', 'leave unchanged', (string) $alreadyCanonicalCount, percent($alreadyCanonicalCount, $variantCount), 'fixed.txt'],
            ['Not an exception-output assertion', 'skip', (string) $nonAssertCount, percent($nonAssertCount, $variantCount), 'non_asserts.txt'],
            ['Fixer matched but not verified', 'needs passing fixture', (string) $verificationFailureCount, percent($verificationFailureCount, $variantCount), 'non_fixable.txt'],
            ['Unsupported by current fixer', 'needs another fixer', (string) $unsupportedCount, percent($unsupportedCount, $variantCount), 'non_fixable.txt'],
            ['Total unique variants', '', (string) $variantCount, '100.00%', ''],
        ]),
        '',
        '## Fixtures',
        renderTable([
            ['Metric', 'Count'],
            ['Copied source fixture pairs', (string) $fixtureStats['pairs']],
            ['Verified passing rewrites', (string) $fixtureStats['verified']],
            ['No current fixer rewrite', (string) $fixtureStats['unchanged']],
            ['Rewrite failed verification', (string) $fixtureStats['failed']],
        ]),
        '',
        '## Reason Counts',
        renderReasonTable($coverage['reasonCounts'], $variantCount),
    ];

    return implode("\n", $lines) . "\n";
}

/**
 * @param array<string, int> $reasonCounts
 */
function reasonCount(array $reasonCounts, string $reason): int
{
    return $reasonCounts[$reason] ?? 0;
}

/**
 * @param list<array{0: array<string, mixed>, 1: array{status: string, reason: string}}> $entries
 * @param array<string, array<string, mixed>> $fixtureByKey
 */
function renderCoverageEntries(string $title, array $entries, array $fixtureByKey): string
{
    $lines = [
        '# ' . $title,
        '',
        'Entries: ' . count($entries),
    ];

    foreach ($entries as [$variant, $classification]) {
        appendCoverageEntry($lines, $variant, $classification, $fixtureByKey);
    }

    return implode("\n", $lines) . "\n";
}

/**
 * @param array<string, int> $reasonCounts
 */
function renderReasonTable(array $reasonCounts, int $variantCount): string
{
    $rows = [['Action', 'Reason', 'Count', 'Share']];
    foreach ($reasonCounts as $reason => $count) {
        [$status, $message] = explode(': ', $reason, 2);
        $rows[] = [statusLabel($status), $message, (string) $count, percent($count, $variantCount)];
    }

    return renderTable($rows);
}

function statusLabel(string $status): string
{
    return match ($status) {
        'covered' => 'ok',
        'not-actionable' => 'skip',
        'not-covered' => 'unsupported',
        default => $status,
    };
}

/**
 * @param list<list<string>> $rows
 */
function renderTable(array $rows): string
{
    $widths = [];
    foreach ($rows as $row) {
        foreach ($row as $i => $cell) {
            $widths[$i] = max($widths[$i] ?? 0, strlen($cell));
        }
    }

    $lines = [];
    foreach ($rows as $rowIndex => $row) {
        $line = [];
        foreach ($row as $i => $cell) {
            $line[] = str_pad($cell, $widths[$i]);
        }
        $lines[] = rtrim(implode('  ', $line));
        if ($rowIndex === 0) {
            $separator = [];
            foreach ($row as $i => $_) {
                $separator[] = str_repeat('-', $widths[$i]);
            }
            $lines[] = rtrim(implode('  ', $separator));
        }
    }

    return implode("\n", $lines);
}

function percent(int $part, int $total): string
{
    if ($total === 0) {
        return '0.00%';
    }

    return sprintf('%.2f%%', ($part / $total) * 100);
}

/**
 * @param list<string> $lines
 * @param array{key: string, variant: string, source: string, sourcePath: string, line: int, statement: string|null, catchVar: string|null, expectedOutput: string|null} $variant
 * @param array{status: string, reason: string} $classification
 * @param array<string, array{key: string, statement: string, catchVar: string, source: string, sourcePath: string, line: int, fixtureOld?: string, fixtureNew?: string, fixtureDiff?: string, fixtureVerified?: bool, fixtureFailure?: string|null, rewritten?: bool}> $fixtureByKey
 */
function appendCoverageEntry(array &$lines, array $variant, array $classification, array $fixtureByKey): void
{
    $lines[] = '';
    $lines[] = '### ' . $classification['reason'];
    $lines[] = 'first seen: ' . $variant['source'] . ':' . $variant['line'];
    $lines[] = 'variant:';
    foreach (explode("\n", trim(str_replace(["\r\n", "\r"], "\n", $variant['variant']))) as $line) {
        $lines[] = '    ' . $line;
    }

    if (isset($fixtureByKey[$variant['key']])) {
        $fixture = $fixtureByKey[$variant['key']];
        if (isset($fixture['fixtureOld'])) {
            $lines[] = 'fixture old: ' . $fixture['fixtureOld'];
            $lines[] = 'fixture new: ' . $fixture['fixtureNew'];
            $lines[] = 'fixture diff: ' . $fixture['fixtureDiff'];
        }
        if (($fixture['fixtureVerified'] ?? false) === true) {
            $lines[] = 'fixture result: verified passing rewrite';
        } elseif (($fixture['fixtureFailure'] ?? null) !== null) {
            $lines[] = 'fixture result: rewrite failed verification: ' . reportLine($fixture['fixtureFailure']);
        } else {
            $lines[] = 'fixture result: no current fixer rewrite';
        }
    }
}

function reportLine(string $text): string
{
    $text = str_replace("\0", '\\0', $text);
    $text = str_replace(["\r", "\n", "\t"], ' ', $text);
    if (strlen($text) <= 240) {
        return $text;
    }

    return substr($text, 0, 237) . '...';
}

/**
 * @param array{key: string, variant: string, source: string, sourcePath: string, line: int, statement: string|null, catchVar: string|null, expectedOutput: string|null} $variant
 * @param array<string, array{key: string, statement: string, catchVar: string, source: string, sourcePath: string, line: int, fixtureOld?: string, fixtureNew?: string, fixtureDiff?: string, fixtureVerified?: bool, fixtureFailure?: string|null, rewritten?: bool}> $fixtureByKey
 * @return array{status: string, reason: string}
 */
function classifyGetMessageLine(array $variant, array $fixtureByKey): array
{
    if (variantComparesMessage($variant['variant'])) {
        return ['status' => 'not-actionable', 'reason' => 'message is compared, not emitted'];
    }

    if ($variant['statement'] !== null && $variant['catchVar'] !== null) {
        if (isAlreadyCanonicalStatement($variant['statement'], $variant['catchVar'])) {
            return ['status' => 'covered', 'reason' => 'already canonical'];
        }

        if (($fixtureByKey[$variant['key']]['fixtureFailure'] ?? null) !== null) {
            return ['status' => 'not-covered', 'reason' => 'rewrite failed verification'];
        }

        if (($fixtureByKey[$variant['key']]['rewritten'] ?? false) === true) {
            return ['status' => 'covered', 'reason' => 'fixer rewrites this output shape'];
        }
    }

    if (expectedOutputLooksLikeNoExceptionPath($variant['expectedOutput'])) {
        return ['status' => 'not-actionable', 'reason' => 'not emitted by current expected output'];
    }

    if ($variant['statement'] === null || $variant['catchVar'] === null) {
        return ['status' => 'not-covered', 'reason' => 'not an echo statement'];
    }

    $normalized = normalizeCode($variant['statement']);
    if (str_contains($normalized, '->getPrevious()->getMessage(')) {
        return ['status' => 'not-covered', 'reason' => 'previous exception message'];
    }

    if (statementLooksLikePreviousExceptionOutput($normalized)) {
        return ['status' => 'not-covered', 'reason' => 'previous exception message'];
    }

    if (preg_match('~->(?:getCode|getFile|getLine|getTrace|getTraceAsString)\(~', $normalized)) {
        return ['status' => 'not-covered', 'reason' => 'custom output with other exception metadata'];
    }

    if (statementIncludesCatchClass($variant['statement'], $variant['catchVar'])) {
        return ['status' => 'not-covered', 'reason' => 'custom class-aware output'];
    }

    if (!str_contains($normalized, $variant['catchVar'] . '->getMessage(')) {
        return ['status' => 'not-covered', 'reason' => 'getMessage() is not a direct call on the catch variable'];
    }

    return ['status' => 'not-covered', 'reason' => 'custom output around getMessage()'];
}

function variantComparesMessage(string $variant): bool
{
    $normalized = normalizeCode($variant);

    return preg_match('~->getMessage\(\)(?:===|!==|==|!=)~', $normalized) === 1
        || preg_match('~(?:===|!==|==|!=).*->getMessage\(\)~', $normalized) === 1;
}

function statementIncludesCatchClass(string $statement, string $catchVar): bool
{
    $normalized = normalizeCode($statement);
    $lowerNormalized = strtolower($normalized);

    return str_contains($normalized, $catchVar . '::class')
        || str_contains($lowerNormalized, 'get_class(' . strtolower($catchVar) . ')');
}

function statementLooksLikePreviousExceptionOutput(string $normalized): bool
{
    return str_contains($normalized, '$previous::class')
        || str_contains($normalized, '$previous->getMessage(');
}

function expectedOutputLooksLikeNoExceptionPath(?string $expectedOutput): bool
{
    if ($expectedOutput === null) {
        return false;
    }

    $lines = preg_split('/\R/', trim(str_replace(["\r\n", "\r"], "\n", $expectedOutput)));
    if ($lines === false || $lines === ['']) {
        return true;
    }

    $meaningfulLines = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $meaningfulLines[] = $line;
        }
    }
    if ($meaningfulLines === []) {
        return true;
    }

    foreach ($meaningfulLines as $line) {
        if (!preg_match('/^(?:[-*]+\s*)?(?:done|ok|success)[!.]?(?:\s*[-*]+)?$/i', $line)) {
            return false;
        }
    }

    return true;
}

function isAlreadyCanonicalStatement(string $statement, string $catchVar): bool
{
    $normalized = normalizeCode($statement);

    return $normalized === 'echo' . $catchVar . '::class,": ",' . $catchVar . '->getMessage();'
        || $normalized === 'echo' . $catchVar . '::class,": ",' . $catchVar . '->getMessage(),"\\n";';
}

function writeTextFile(string $path, string $contents): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
        throw new RuntimeException("Cannot create output directory: $dir");
    }
    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException("Cannot write $path");
    }
}
