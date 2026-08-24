--TEST--
User filter releases input buckets added by a warning handler
--FILE--
<?php

class ReentrantFilter extends php_user_filter
{
	private bool $firstCall = true;

	public function filter($in, $out, &$consumed, bool $closing): int
	{
		if ($this->firstCall) {
			$this->firstCall = false;
			$GLOBALS['brigade'] = $in;
			$GLOBALS['bucket'] = stream_bucket_new($this->stream, 'refilled');
		}

		return PSFS_PASS_ON;
	}
}

stream_filter_register('test.reentrant', ReentrantFilter::class);

set_error_handler(static function (int $severity, string $message): bool
{
	if (str_contains($message, 'Unprocessed filter buckets')) {
		stream_bucket_append($GLOBALS['brigade'], $GLOBALS['bucket']);
		echo "Handled warning\n";
		return true;
	}

	return false;
});

$stream = fopen('php://memory', 'w+');
stream_filter_append($stream, 'test.reentrant', STREAM_FILTER_WRITE);

var_dump(fwrite($stream, 'input'));
fclose($stream);

?>
--EXPECT--
Handled warning
int(0)
