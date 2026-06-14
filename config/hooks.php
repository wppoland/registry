<?php
/**
 * Boot order: services listed here are resolved from the container and have
 * their registerHooks() called during Plugin::boot(). Each must implement
 * Registry\Contract\HasHooks and be registered in config/services.php.
 *
 * Admin-only services are appended only in wp-admin, where they are registered
 * in the container — listing them on the front end would throw.
 *
 * @package Registry
 *
 * @return array<class-string>
 */

declare(strict_types=1);

use Registry\Account\MyRegistries;
use Registry\Admin\Settings as AdminSettings;
use Registry\PostType\GiftRegistry;
use Registry\Service\AddToRegistry;
use Registry\Service\PublicView;
use Registry\Service\PurchaseTracker;

defined('ABSPATH') || exit;

$hooks = [
    GiftRegistry::class,
    PurchaseTracker::class,
    PublicView::class,
    AddToRegistry::class,
    MyRegistries::class,
];

if (is_admin()) {
    $hooks[] = AdminSettings::class;
}

return $hooks;
