<?php

declare(strict_types=1);

namespace Registry;

defined('ABSPATH') || exit;

/**
 * Idempotent schema/version migrations, run on every boot. Compares a stored
 * option against VERSION and applies forward steps as needed.
 */
final class Migrator
{
    private const OPTION = 'registry_db_version';

    public function maybeMigrate(): void
    {
        $current = (string) get_option(self::OPTION, '0');

        if (version_compare($current, VERSION, '>=')) {
            return;
        }

        // Rewrite rules for the CPT permalink and the My Account endpoint were
        // registered during this same boot (on init); flush once so the new
        // structure takes effect after install or upgrade.
        flush_rewrite_rules(false);

        update_option(self::OPTION, VERSION, false);
    }
}
