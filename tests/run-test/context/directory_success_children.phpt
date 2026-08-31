--TEST--
Test Directory child allocation - named and anonymous paths - parent relationship without creation
--FILE--
<?php

$directory = Workspace::directory();

foreach (['file', 'directory'] as $method) {
    echo "=== $method ===\n";

    $named = $directory->$method('fixture');
    var_dump($directory->$method('fixture')->path === $named->path);

    foreach ([$named, $directory->$method(), $directory->$method()] as $child) {
        echo $child, "\n";
        var_dump(
            dirname($child->path) === $directory->path,
            file_exists($child),
        );
    }
}

var_dump(file_exists($directory));

?>
--EXPECTF--
=== file ===
bool(true)
%s%edirectory-1%efixture
bool(true)
bool(false)
%s%edirectory-1%efile-1
bool(true)
bool(false)
%s%edirectory-1%efile-2
bool(true)
bool(false)
=== directory ===
bool(true)
%s%edirectory-1%efixture
bool(true)
bool(false)
%s%edirectory-1%edirectory-1
bool(true)
bool(false)
%s%edirectory-1%edirectory-2
bool(true)
bool(false)
bool(false)
