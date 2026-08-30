--TEST--
Bug GH-8750 (Can not create VT_ERROR variant type)
--EXTENSIONS--
com_dotnet
--SKIPIF--
<?php
$provider = "Microsoft.ACE.OLEDB.12.0";
$filename = sys_get_temp_dir()
    . DIRECTORY_SEPARATOR
    . 'gh8750-'
    . bin2hex(random_bytes(8))
    . '.mdb';
$filename = str_replace("/", "\\", $filename);
$catalog = new com("ADOX.Catalog");
try {
    $catalog->Create("Provider=$provider;Data Source=$filename");
} catch (com_exception) {
    die("skip $provider provider not available");
}
$catalog = null;
@unlink($filename);
?>
--FILE--
<?php
$filename = str_replace("/", "\\", TestPath::file('database.mdb'));

$catalog = new com("ADOX.Catalog");
$catalog->Create("Provider=Microsoft.ACE.OLEDB.12.0;Data Source=$filename");
$catalog = null;

$db = new com("ADODB.Connection");
$db->ConnectionString = "Provider=Microsoft.ACE.OLEDB.12.0;Data Source=$filename";
$db->Mode = 1; // adModeRead
$db->Open();
// adSchemaProviderSpecific, *missing*, JET_SCHEMA_USERROSTER
$rs = $db->OpenSchema(-1, new variant(DISP_E_PARAMNOTFOUND, VT_ERROR), "{947bb102-5d43-11d1-bdbf-00c04fb92675}");
// manual counting since rs.RecordCount is -1 (not supported)
$i = 0;
while (!$rs->EOF) {
    $rs->MoveNext();
    $i++;
}
$rs->Close();
$db->Close();
var_dump($i);
?>
--EXPECT--
int(1)
