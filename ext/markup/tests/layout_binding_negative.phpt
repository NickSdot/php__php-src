--TEST--
Markup layout binding: invalid targets, delimiters, and $this writes fail
--EXTENSIONS--
markup
--SKIPIF--
<?php
if (!function_exists('proc_open')) die('skip proc_open() not available');
?>
--FILE--
<?php
function show_parse_failure(string $name, string $code): void {
    $php = getenv('TEST_PHP_EXECUTABLE');
    $process = proc_open(
        [$php, '-d', 'display_errors=1', '-d', 'log_errors=0', '-r', $code],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
    );
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
    $line = strtok($combined, "\n");

    echo $name, ': ', $line, "\n";
}

show_parse_failure('missing-target-braces', <<<'PHP'
$page = new stdClass();
<<$page>>
    <p>x</p>
<</>>;
PHP);

show_parse_failure('missing-close', <<<'PHP'
$page = new stdClass();
<<{$page}>>
    <p>x</p>
PHP);

show_parse_failure('non-object-target', <<<'PHP'
$page = 'not an object';
<<{$page}>>
    <p>x</p>
<</>>;
PHP);

show_parse_failure('missing-context-target', <<<'PHP'
$page = new stdClass();
<<{$page}>>
    <p>x</p>
<</>>;
PHP);

show_parse_failure('assign-this', <<<'PHP'
final class Page implements Markup\Renderable {}
$page = new Page();
<<{$page}>>
    $this = null;
<</>>;
PHP);

show_parse_failure('unset-this', <<<'PHP'
final class Page implements Markup\Renderable {}
$page = new Page();
<<{$page}>>
    unset($this);
<</>>;
PHP);

show_parse_failure('global-this', <<<'PHP'
final class Page implements Markup\Renderable {}
$page = new Page();
<<{$page}>>
    global $this;
<</>>;
PHP);

show_parse_failure('foreach-this', <<<'PHP'
final class Page implements Markup\Renderable {}
$page = new Page();
<<{$page}>>
    foreach ([1] as $this) {}
<</>>;
PHP);

show_parse_failure('renderable-outside-binding', <<<'PHP'
final class Page implements Markup\Html {
    public function toHtml(): Markup\Html {
        return $this->renderable;
    }
}
$page = new Page();
$page->renderable;
PHP);

show_parse_failure('assign-renderable', <<<'PHP'
final class Page implements Markup\Html {
    public function toHtml(): Markup\Html {
        $this->renderable = <p>replacement</p>;
        return <p>unreachable</p>;
    }
}
$page = new Page();
<<{$page}>>
    <p>body</p>
<</>>;
PHP);

show_parse_failure('unset-renderable', <<<'PHP'
final class Page implements Markup\Html {
    public function toHtml(): Markup\Html {
        unset($this->renderable);
        return <p>unreachable</p>;
    }
}
$page = new Page();
<<{$page}>>
    <p>body</p>
<</>>;
PHP);
?>
--EXPECTF--
missing-target-braces: Parse error: syntax error, unexpected token "<<", expecting end of file
missing-close: Parse error: syntax error, unexpected end of file
non-object-target: Fatal error: Uncaught TypeError: Markup layout binding target must implement Markup\Renderable%s
missing-context-target: Fatal error: Uncaught TypeError: Markup layout binding target must implement Markup\Renderable%s
assign-this: Fatal error: Cannot re-assign $this
unset-this: Fatal error: Cannot unset $this
global-this: Fatal error: Cannot use $this as global variable
foreach-this: Fatal error: Cannot re-assign $this
renderable-outside-binding: Fatal error: Uncaught Error: Markup renderable content is not available%s
assign-renderable: Fatal error: Uncaught Error: Cannot write to get-only virtual property Page::$renderable%s
unset-renderable: Fatal error: Uncaught Error: Cannot unset get-only virtual property Page::$renderable%s
