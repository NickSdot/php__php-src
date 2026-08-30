<?php

// This file contains helper functions for testing open_basedir configuration
// Care must be taken with where the directories are created because different
// SAPIs set the working directory differently. So simply creating a directory
// relative to the current working directory like this: mkdir("blah") might
// actually create it in several different places depending on the SAPI..!
//
// Note also depending on the version of php being tested, so the open_basedir
// configuration may or may not be changeable from a script (PHP_INI_SYSTEM).
//
// For this reason we set the open_basedir to . (current directory) and then
// move around to various directories for testing using chdir(). This is NOT
// recommended for production use as . bypasses all semblances of security..!
//
// Although safe mode has been removed in php 6.0, open_basedir is still valid.
//      See http://www.php.net/features.safe-mode for more information

function create_directories($selectWorkspace = TRUE) {
    global $testDirectory;

    if ($selectWorkspace) {
        TestContext::useRelativeWorkspace();
    }
    $testDirectory = TestPath::directory('test');
    var_dump(mkdir($testDirectory));
    $okDirectory = $testDirectory->directory('ok');
    var_dump(mkdir($okDirectory));
    $badDirectory = $testDirectory->directory('bad');
    var_dump(mkdir($badDirectory));
    file_put_contents($okDirectory->file('ok.txt'), "Hello World!");
    file_put_contents($badDirectory->file('bad.txt'), "Hello World!");
    chdir(dirname($testDirectory));
}

function test_open_basedir_error($function) {
    global $savedDirectory;
    var_dump($function("../bad"));
    var_dump($function("../bad/bad.txt"));
    var_dump($function(".."));
    var_dump($function("../"));
    var_dump($function("/"));
    var_dump($function("../bad/."));
    $directory = $savedDirectory;
    var_dump($function($directory."/test/bad/bad.txt"));
    var_dump($function($directory."/test/bad/../bad/bad.txt"));
}

function test_open_basedir_before($function, $change = TRUE, $selectWorkspace = TRUE) {
    global $initdir, $testDirectory, $savedDirectory;
    echo "*** Testing open_basedir configuration [$function] ***\n";
    create_directories($selectWorkspace);
    $directory = dirname($testDirectory);
    if (isset($initdir)) {
        $initdir = $directory;
    }
    $savedDirectory = $directory;
    var_dump(chdir($directory));

    // Optionally change directory
    if ($change == TRUE) {
        var_dump(chdir($directory."/test/ok"));
    }
}

function test_open_basedir_after($function) {
    echo "*** Finished testing open_basedir configuration [$function] ***\n";
}

// This is used by functions that return an array on success
function test_open_basedir_array($function) {
    global $savedDirectory;

    test_open_basedir_before($function);
    test_open_basedir_error($function);
    var_dump(is_array($function("./../.")));
    var_dump(is_array($function("../ok")));
    var_dump(is_array($function("ok.txt")));
    var_dump(is_array($function("../ok/ok.txt")));
    $directory = $savedDirectory;
    var_dump(is_array($function($directory."/test/ok/ok.txt")));
    var_dump(is_array($function($directory."/test/ok/../ok/ok.txt")));
    test_open_basedir_after($function);
}

function test_open_basedir($function) {
    global $savedDirectory;
    test_open_basedir_before($function);
    test_open_basedir_error($function);
    var_dump($function("./../."));
    var_dump($function("../ok"));
    var_dump($function("ok.txt"));
    var_dump($function("../ok/ok.txt"));
    $directory = $savedDirectory;
    var_dump($function($directory."/test/ok/ok.txt"));
    var_dump($function($directory."/test/ok/../ok/ok.txt"));
    test_open_basedir_after($function);
}

?>
