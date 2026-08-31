--TEST--
Dom\HTMLDocument GB18030 encoding test
--EXTENSIONS--
dom
--FILE--
<?php

$dom = Dom\HTMLDocument::createFromFile(__DIR__ . "/gb18030.html");
var_dump($dom->charset);
$dom->documentElement->firstChild->nextElementSibling->textContent = "茅";
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
string(7) "gb18030"
<!DOCTYPE html><html><head>
<meta charset="gb18030">
</head>
<body>é</body></html>
bool(true)
--- After changing encoding to UTF-8 ---
<!DOCTYPE html><html><head>
<meta charset="gb18030">
</head>
<body>茅</body></html>
