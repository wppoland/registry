<?php
/**
 * Autoloading: prefer Composer's optimized classmap when present, otherwise fall
 * back to a minimal PSR-4 autoloader so the plugin still boots if vendor/ is
 * somehow absent. This plugin is self-contained — no external runtime packages.
 *
 * @package Registry
 */

declare(strict_types=1);

namespace Registry;

defined('ABSPATH') || exit;

$registry_composer = __DIR__ . '/vendor/autoload.php';
if (is_readable($registry_composer)) {
    require_once $registry_composer;
    return;
}

spl_autoload_register(static function (string $class): void {
    $prefix  = 'Registry\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative = substr($class, $len);
    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (is_readable($file)) {
        require_once $file;
    }
});
