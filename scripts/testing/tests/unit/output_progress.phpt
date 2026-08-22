--TEST--
Terminal progress is cleared after completion
--FILE--
<?php
require dirname(__DIR__, 4) . '/scripts/testing/autoload.php';

use PHP\Testing\Output;

function render(Output $output): string
{
	ob_start();

	$output->startProgress(['Coverage', '', 'Base', 'Tree', '']);
	$output->progress('Building base');
	$output->progress('Running base tests');
	$output->finishProgress();

	return ob_get_clean();
}

$interactive = render(new Output(true));

var_dump(str_contains($interactive, "\033["));
var_dump(str_ends_with($interactive, "Coverage\n\nBase\nTree\n\n"));

echo render(new Output(false));
?>
--EXPECT--
bool(true)
bool(true)
Coverage

Base
Tree

Building base
Running base tests
