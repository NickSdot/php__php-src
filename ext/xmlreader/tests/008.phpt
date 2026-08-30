--TEST--
XMLReader: libxml2 XML Reader, DTD
--EXTENSIONS--
xmlreader
--FILE--
<?php

$xmlstring = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<!DOCTYPE LIST SYSTEM "dtdexample.dtd">
<LIST>
<MOVIE ID="x200338360">
<TITLE>Move Title 1</TITLE>
<ORGTITLE/><LOC>Location 1</LOC>
<INFO/>
</MOVIE>
<MOVIE ID="m200338361">
<TITLE>Move Title 2</TITLE>
<ORGTITLE/>
<LOC>Location 2</LOC>
<INFO/>
</MOVIE>
</LIST>';

TestContext::useRelativeWorkspace();
$dtd = TestPath::file('dtdexample.dtd');
copy(__DIR__ . '/dtdexample.dtd', $dtd);
$dtdfile = rawurlencode($dtd->directory) . '/dtdexample.dtd';
$file = TestPath::file();
file_put_contents($file, $xmlstring);


$reader = new XMLReader();
$reader->open($file);
$reader->setParserProperty(XMLREADER::LOADDTD, TRUE);
$reader->setParserProperty(XMLREADER::VALIDATE, TRUE);
while($reader->read());
if ($reader->isValid()) {
    echo "file DTD: ok\n";
}
$reader->close();
$xmlstring = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<!DOCTYPE LIST SYSTEM "file:///' . $dtdfile. '">
<LIST>
<MOVIE ID="x200338360">
<TITLE>Move Title 1</TITLE>
<ORGTITLE/><LOC>Location 1</LOC>
<INFO/>
</MOVIE>
<MOVIE ID="m200338361">
<TITLE>Move Title 2</TITLE>
<ORGTITLE/>
<LOC>Location 2</LOC>
<INFO/>
</MOVIE>
</LIST>';

$reader = new XMLReader();
$reader->XML($xmlstring);

$reader->setParserProperty(XMLREADER::LOADDTD, TRUE);
$reader->setParserProperty(XMLREADER::VALIDATE, TRUE);
while($reader->read());
if ($reader->isValid()) {
    echo "string DTD: ok\n";
}
?>
--EXPECT--
file DTD: ok
string DTD: ok
