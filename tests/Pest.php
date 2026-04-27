<?php

spl_autoload_register(static function (string $class): void {
    $prefix = 'Corepine\\Support\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__, 2) . '/support/src/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});

$supportHelpers = dirname(__DIR__, 2) . '/support/src/helpers.php';

if (is_file($supportHelpers)) {
    require_once $supportHelpers;
}

use Corepine\Modal\Tests\TestCase;

uses(TestCase::class)->in('Feature');
