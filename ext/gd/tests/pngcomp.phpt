--TEST--
png compression test
--EXTENSIONS--
gd
--SKIPIF--
<?php
    if (!function_exists("imagecreatefrompng") || !function_exists("imagepng")) {
        die("skip png support unavailable");
    }
?>
--FILE--
<?php
    $cwd = __DIR__;
    $output = Workspace::file();


        $im = imagecreatetruecolor(20,20);
        imagefilledrectangle($im, 5,5, 10,10, 0xffffff);
	try {
		imagepng($im, $output->path, -2);
	} catch (\ValueError $e) {
		echo $e::class, ': ', $e->getMessage(), PHP_EOL;
	}
	try {
		imagepng($im, $output->path, 10);
	} catch (\ValueError $e) {
		echo $e::class, ': ', $e->getMessage(), PHP_EOL;
	}
    	echo "PNG compression test: ";
        imagepng($im, $output->path, 9);

        $im2 = imagecreatefrompng($output);
        $col = imagecolorat($im2, 8,8);
        if ($col == 0xffffff) {
                echo "ok\n";
        }

?>
--EXPECT--
ValueError: imagepng(): Argument #3 ($quality) must be between -1 and 9
ValueError: imagepng(): Argument #3 ($quality) must be between -1 and 9
PNG compression test: ok
