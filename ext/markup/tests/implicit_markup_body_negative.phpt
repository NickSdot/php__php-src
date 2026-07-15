--TEST--
Markup statements: negative parser and return-type activation boundaries
--EXTENSIONS--
markup
--SKIPIF--
<?php
if (!function_exists('proc_open')) die('skip proc_open() not available');
?>
--FILE--
<?php
function show_failure(string $name, string $code): void {
    $php = getenv('TEST_PHP_EXECUTABLE');
    $cmd = [
        $php,
        '-d', 'display_errors=1',
        '-d', 'log_errors=0',
        '-r', $code,
    ];
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($cmd, $descriptors, $pipes);
    if (!is_resource($process)) {
        echo $name, ": proc_open failed\n";
        return;
    }

    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    $combined = trim($output . "\n" . $error);
    $combined = preg_replace('/ in Command line code on line \d+/', '', $combined);
    $combined = preg_replace("/\nStack trace:\n.*$/s", '', $combined);
    $line = strtok($combined, "\n");

    echo $name, ': ', $line, "\n";
}

show_failure('top-level-markup-statement', <<<'PHP'
<p>x</p>
PHP);

show_failure('missing-return-type', <<<'PHP'
function f() {
    <p>x</p>
}
PHP);

show_failure('wrong-return-type', <<<'PHP'
function f(): string {
    <p>x</p>
}
PHP);

show_failure('interface-alone-does-not-activate', <<<'PHP'
class C implements Markup\Html {
    public function toHtml() {
        <p>x</p>
    }
}
PHP);

show_failure('intersection-return-type-does-not-activate', <<<'PHP'
function f(): Markup\Html&Stringable {
    <p>x</p>
}
PHP);

show_failure('unbraced-if-body', <<<'PHP'
function f(): Markup\Html {
    if (true) <p>x</p>
}
PHP);

show_failure('semicolonless-plain-php-between-markup', <<<'PHP'
function f(): Markup\Html {
    <p>x</p>
    Hello::class
    <p>y</p>
}
PHP);

show_failure('assignment-expression-still-needs-semicolon', <<<'PHP'
function f(): Markup\Html {
    $x = <p>x</p>
}
PHP);

show_failure('return-expression-still-needs-semicolon', <<<'PHP'
function f(): Markup\Html {
    return <p>x</p>
}
PHP);
?>
--EXPECT--
top-level-markup-statement: Fatal error: Markup statements are only allowed in functions returning Markup\Html
missing-return-type: Fatal error: Markup statements are only allowed in functions returning Markup\Html
wrong-return-type: Fatal error: Markup statements are only allowed in functions returning Markup\Html
interface-alone-does-not-activate: Fatal error: Markup statements are only allowed in functions returning Markup\Html
intersection-return-type-does-not-activate: Fatal error: Markup statements are only allowed in functions returning Markup\Html
unbraced-if-body: Parse error: syntax error, unexpected token "<"
semicolonless-plain-php-between-markup: Parse error: syntax error, unexpected token ">"
assignment-expression-still-needs-semicolon: Parse error: syntax error, unexpected token "}"
return-expression-still-needs-semicolon: Parse error: syntax error, unexpected token "}", expecting ";"
