<?php
/**
 * Service wiring. Returns a closure that registers every service in the
 * container. Services are thin and resolved lazily; admin-only services are only
 * registered in wp-admin so the front end stays lean.
 *
 * @package Registry
 */

declare(strict_types=1);

use Registry\Account\MyRegistries;
use Registry\Admin\Settings as AdminSettings;
use Registry\Container;
use Registry\Migrator;
use Registry\PostType\GiftRegistry;
use Registry\Service\AddToRegistry;
use Registry\Service\PublicView;
use Registry\Service\PurchaseTracker;
use Registry\Service\RegistryManager;
use Registry\Support\Settings;

defined('ABSPATH') || exit;

return static function (Container $c): void {
    $c->singleton(Migrator::class, static fn (): Migrator => new Migrator());

    $c->singleton(Settings::class, static fn (): Settings => new Settings());

    $c->singleton(GiftRegistry::class, static fn (): GiftRegistry => new GiftRegistry());

    $c->singleton(PurchaseTracker::class, static fn (): PurchaseTracker => new PurchaseTracker());

    $c->singleton(RegistryManager::class, static fn (Container $c): RegistryManager => new RegistryManager(
        $c->get(GiftRegistry::class),
    ));

    $c->singleton(PublicView::class, static fn (Container $c): PublicView => new PublicView(
        $c->get(GiftRegistry::class),
        $c->get(PurchaseTracker::class),
        $c->get(Settings::class),
    ));

    $c->singleton(AddToRegistry::class, static fn (Container $c): AddToRegistry => new AddToRegistry(
        $c->get(RegistryManager::class),
        $c->get(Settings::class),
    ));

    $c->singleton(MyRegistries::class, static fn (Container $c): MyRegistries => new MyRegistries(
        $c->get(RegistryManager::class),
        $c->get(GiftRegistry::class),
        $c->get(PurchaseTracker::class),
        $c->get(Settings::class),
    ));

    if (is_admin()) {
        $c->singleton(AdminSettings::class, static fn (Container $c): AdminSettings => new AdminSettings(
            $c->get(Settings::class),
        ));
    }
};
