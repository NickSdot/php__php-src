--TEST--
Dom\HTMLDocument Shift JIS encoding test
--EXTENSIONS--
dom
--FILE--
<?php

$dom = Dom\HTMLDocument::createFromFile(__DIR__ . "/shift_jis.html");
var_dump($dom->charset);
$dom->documentElement->firstChild->nextElementSibling->textContent .= "ﾃｩ";
$output = $dom->saveHtml();
echo $output, "\n";
$outputFile = Workspace::file();
$dom->saveHtmlFile($outputFile);
var_dump(file_get_contents($outputFile) === $output);

echo "--- After changing encoding to UTF-8 ---\n";
$dom->charset = "UTF-8";
echo $dom->saveHtml(), "\n";

?>
--EXPECT--
string(9) "Shift_JIS"
<!DOCTYPE html><html><head>
<meta charset="shift_jis">
</head>
<body>
    やあ
?</body></html>
bool(true)
--- After changing encoding to UTF-8 ---
<!DOCTYPE html><html><head>
<meta charset="shift_jis">
</head>
<body>
    繧�縺�
ﾃｩ</body></html>
