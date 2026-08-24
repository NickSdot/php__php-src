--TEST--
User filters release unprocessed buckets for PSFS_FEED_ME and PSFS_ERR_FATAL
--FILE--
<?php

class FeedFilter extends php_user_filter
{
	public function filter($in, $out, &$consumed, bool $closing): int {
		return PSFS_FEED_ME;
	}
}

class FatalFilter extends php_user_filter
{
	private bool $emitted = false;

	public function filter($in, $out, &$consumed, bool $closing): int
	{
		if ($closing && !$this->emitted) {
			$this->emitted = true;
			$bucket = stream_bucket_new($this->stream, "x");
			stream_bucket_append($out, $bucket);
		}

		return PSFS_ERR_FATAL;
	}
}

stream_filter_register("test.feed", FeedFilter::class);

$stream = fopen("php://memory", "w+");
fwrite($stream, "x\nabcdef");
rewind($stream);

var_dump(fgets($stream));
var_dump(stream_filter_append($stream, "test.feed", STREAM_FILTER_READ));
fclose($stream);

stream_filter_register("test.fatal", FatalFilter::class);

$stream = fopen("php://memory", "w+");
$filter = stream_filter_append($stream, "test.fatal", STREAM_FILTER_WRITE);

var_dump(stream_filter_remove($filter));
fclose($stream);

?>
--EXPECTF--
string(2) "x
"

Warning: stream_filter_append(): Unprocessed filter buckets remaining on input brigade in %s on line %d
resource(%d) of type (stream filter)

Warning: stream_filter_remove(): Unable to flush filter, not removing in %s on line %d
bool(false)
