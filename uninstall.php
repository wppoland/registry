<?php
/**
 * Uninstall cleanup for Registry.
 *
 * Runs when the plugin is deleted from wp-admin. Removes the plugin's options.
 * Customer-created gift registries (the gift_registry custom post type) and the
 * purchased-quantity meta are intentionally left in place: they are user content
 * that should survive a reinstall and can be removed manually if desired.
 *
 * @package Registry
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('registry_settings');
delete_option('registry_db_version');
