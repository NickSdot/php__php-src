--TEST--
Bug #61948 (CURLOPT_COOKIEFILE '' raises open_basedir restriction)
--EXTENSIONS--
curl
--FILE--
<?php
  $base_dir = Workspace::directory('bug61948');
  $foo_dir = $base_dir->directory('foo');
  mkdir($foo_dir, 0755, true);

  ini_set("open_basedir", $base_dir->path);

  $ch = curl_init();
  var_dump(curl_setopt($ch, CURLOPT_COOKIEFILE, ""));
  var_dump(curl_setopt($ch, CURLOPT_COOKIEFILE, $foo_dir));
  var_dump(curl_setopt($ch, CURLOPT_COOKIEFILE, "c:/xxx/bar"));
?>
--EXPECTF--
%a
bool(true)

Warning: curl_setopt(): open_basedir restriction in effect. File(c:/xxx/bar) is not within the allowed path(s): (%sbug61948) in %sbug61948.%d.php on line %d
bool(false)
