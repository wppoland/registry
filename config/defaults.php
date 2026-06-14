<?php
/**
 * Default settings, merged under the option key `registry_settings`.
 *
 * @package Registry
 *
 * @return array<string, mixed>
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

return [
    // Master switch: render the storefront button, public pages and My Account area.
    'enabled'        => true,
    // Whether guests can buy registry items straight from the public page.
    'allow_purchase' => true,
];
