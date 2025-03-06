<?php

/**
 * Custom error handler to suppress PHPUnit warnings in more recent version of php.
 *
 * @param int    $errno   The level of the error raised
 * @param string $errstr  The error message
 * @param string $errfile The filename that the error was raised in
 *
 * @return bool Returns true to suppress the error, false to let PHP handle it
 */
set_error_handler(function ($errno, $errstr, $errfile) {
    // If the error comes from a vendor file (e.g. from PHPUnit’s directory), ignore it.
    if (strpos($errfile, '/vendor/phpunit/') !== false) {
        return true; // signal that the error has been handled
    }

    // Otherwise, let PHP's normal error handling (or PHPUnit's handler) do its work.
    return false;
});

$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    throw new RuntimeException('Composer autoload.php not found. Please run "composer install" first.');
}

// If the FORCE_C_EXT environment variable is set, we will force the use of the C extension.
if (getenv('FORCE_C_EXT')) {
    require_once __DIR__ . '/force_c_ext.php';
}

return require $autoloadPath;
