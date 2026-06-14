<?php
/**
 * Constants PHPStan needs to analyse the plugin without bootstrapping WordPress.
 * These are defined for real in registry.php at runtime.
 *
 * @package Registry
 */

declare(strict_types=1);

namespace {
    if (! defined('ABSPATH')) {
        define('ABSPATH', '/tmp/wordpress/');
    }
    if (! defined('REGISTRY_DIR')) {
        define('REGISTRY_DIR', '/tmp/registry/');
    }
    if (! defined('REGISTRY_URL')) {
        define('REGISTRY_URL', 'https://example.test/wp-content/plugins/registry/');
    }
}

namespace Registry {
    if (! defined('Registry\\VERSION')) {
        define('Registry\\VERSION', '0.1.0');
    }
    if (! defined('Registry\\PLUGIN_FILE')) {
        define('Registry\\PLUGIN_FILE', '/tmp/registry/registry.php');
    }
}
