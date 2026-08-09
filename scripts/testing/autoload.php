<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'PHP\\Testing\\';

    if (str_starts_with($class, $prefix) === false) {
        return;
    }

    require __DIR__ . '/src/' . substr($class, strlen($prefix)) . '.php';
});
