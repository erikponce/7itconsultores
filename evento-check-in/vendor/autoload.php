<?php

spl_autoload_register(function ($class) {
    $prefix_map = [
        'chillerlan\\QRCode\\' => __DIR__ . '/php-qrcode/src/',
        'chillerlan\\Settings\\' => __DIR__ . '/php-settings-container/src/',
    ];

    foreach ($prefix_map as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
