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

/**
 * Remove this plugin's options from the current site.
 */
function registry_uninstall_site(): void
{
    delete_option('registry_settings');
    delete_option('registry_db_version');
}

// Options are per site, so a network uninstall has to visit every site.
if (is_multisite()) {
    foreach (get_sites(['fields' => 'ids', 'number' => 0]) as $registry_site_id) {
        switch_to_blog((int) $registry_site_id);
        registry_uninstall_site();
        restore_current_blog();
    }
    unset($registry_site_id);
} else {
    registry_uninstall_site();
}
