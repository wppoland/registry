<?php
/**
 * Plugin Name:       Registry - Gift Registry for WooCommerce
 * Plugin URI:        https://plogins.com/registry/
 * Description:        Let customers create shareable gift registries for weddings, baby showers and events.
 * Version:           0.1.4
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Requires Plugins:  woocommerce
 * Author:            WPPoland.com
 * Author URI:        https://wppoland.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       registry
 * Domain Path:       /languages
 * WC requires at least: 8.0
 *
 * @package Registry
 */

declare(strict_types=1);

namespace Registry;

defined('ABSPATH') || exit;

const VERSION     = '0.1.4';
const PLUGIN_FILE = __FILE__;

define('REGISTRY_DIR', plugin_dir_path(__FILE__));
define('REGISTRY_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/autoload.php';

// On activation, register the CPT + My Account endpoint so their rewrite rules
// exist, seed default settings, then flush. Registration is idempotent and safe.
register_activation_hook(__FILE__, static function (): void {
    require_once __DIR__ . '/autoload.php';

    (new PostType\GiftRegistry())->register();
    add_rewrite_endpoint(Account\MyRegistries::ENDPOINT, EP_ROOT | EP_PAGES);

    if (false === get_option('registry_settings', false)) {
        /** @var array<string, mixed> $defaults */
        $defaults = require __DIR__ . '/config/defaults.php';
        add_option('registry_settings', $defaults);
    }

    flush_rewrite_rules(false);
});

register_deactivation_hook(__FILE__, static function (): void {
    flush_rewrite_rules(false);
});

// HPOS + cart/checkout blocks compatibility.
add_action('before_woocommerce_init', static function (): void {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

add_action('plugins_loaded', static function (): void {
    if (! class_exists('WooCommerce')) {
        add_action('admin_notices', static function (): void {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Registry - Gift Registry for WooCommerce requires WooCommerce to be active.', 'registry');
            echo '</p></div>';
        });
        return;
    }

    add_action('init', static function (): void {
        Plugin::instance()->boot();
    }, 0);
}, 10);
