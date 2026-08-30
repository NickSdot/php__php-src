--TEST--
Bug #61948 (CURLOPT_COOKIEFILE '' raises open_basedir restriction)
--EXTENSIONS--
curl
--FILE--
<?php
  $base_dir = TestPath::directory("bug61948")->path;
  mkdir($base_dir . DIRECTORY_SEPARATOR . "foo", 0755, true);

  ini_set("open_basedir", $base_dir);

  $ch = curl_init();
  var_dump(curl_setopt($ch, CURLOPT_COOKIEFILE, ""));
  var_dump(curl_setopt($ch, CURLOPT_COOKIEFILE, "$base_dir/foo"));
  var_dump(curl_setopt($ch, CURLOPT_COOKIEFILE, "c:/xxx/bar"));
?>
--EXPECTF--
%a
bool(true)

Warning: curl_setopt(): open_basedir restriction in effect. File(c:/xxx/bar) is not within the allowed path(s): (%sbug61948) in %sbug61948.php on line %d
bool(false)
