--TEST--
Test that there is no arginfo/zpp mismatch
--SKIPIF--
<?php
if (getenv('SKIP_ASAN')) die("skip Intermittently crashes lsan");
if (getenv('SKIP_MSAN')) die("skip msan misses interceptors for some functions");
?>
--FILE--
<?php

require __DIR__ . "/arginfo_zpp_mismatch.inc";

function cleanupIpcResult($result) {
    if ($result instanceof SysvMessageQueue) {
        msg_remove_queue($result);
    } elseif ($result instanceof SysvSemaphore) {
        sem_remove($result);
    } elseif ($result instanceof SysvSharedMemory) {
        shm_remove($result);
    }
}

function test($function) {
    if (skipFunction($function)) {
        return;
    }

    ob_start();
    if (is_string($function)) {
        echo "Testing $function\n";
    } else {
        echo "Testing " . get_class($function[0]) . "::$function[1]\n";
    }
    try {
        cleanupIpcResult(@$function());
    } catch (Throwable) {
    }
    try {
        if ($function === 'msg_queue_exists') {
            @$function(1);
        } else {
            cleanupIpcResult(@$function(null));
        }
    } catch (Throwable) {
    }
    try {
        cleanupIpcResult(@$function(null, null));
    } catch (Throwable) {
    }
    try {
        if ($function === 'sem_get' || $function === 'shm_attach') {
            cleanupIpcResult(@$function(null, null, 0600));
        } else {
            cleanupIpcResult(@$function(null, null, null));
        }
    } catch (Throwable) {
    }
    try {
        if ($function === 'sem_get') {
            cleanupIpcResult(@$function(null, null, 0600, null));
        } else {
            cleanupIpcResult(@$function(null, null, null, null));
        }
    } catch (Throwable) {
    }
    try {
        cleanupIpcResult(@$function(null, null, null, null, null));
    } catch (Throwable) {
    }
    try {
        cleanupIpcResult(@$function(null, null, null, null, null, null));
    } catch (Throwable) {
    }
    try {
        cleanupIpcResult(@$function(null, null, null, null, null, null, null));
    } catch (Throwable) {
    }
    try {
        cleanupIpcResult(@$function(null, null, null, null, null, null, null, null));
    } catch (Throwable) {
    }
    ob_end_clean();
}

foreach (get_defined_functions()["internal"] as $function) {
    test($function);
}

foreach (get_declared_classes() as $class) {
    try {
        $rc = new ReflectionClass($class);
        $obj = $rc->newInstanceWithoutConstructor();
    } catch (Throwable) {
        continue;
    }

    foreach (get_class_methods($class) as $method) {
        test([$obj, $method]);
    }
}

// var_dump() and debug_zval_dump() print all arguments
?>
===DONE===
--EXPECT--
===DONE===
