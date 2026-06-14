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
    // Storefront "Add to registry" button label (blank = default).
    'button_text'    => '',
    // Optional intro shown above every public registry page.
    'public_intro'   => '',
    // Whether guests can buy registry items straight from the public page.
    'allow_purchase' => true,
];
