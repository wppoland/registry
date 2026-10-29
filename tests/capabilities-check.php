<?php
/**
 * Registry posts are managed by shop staff, not by every Editor, and the
 * mapping leaves manage_woocommerce a plain capability.
 *
 * Run inside wp-env:  wp eval-file tests/capabilities-check.php
 *
 * @package Registry/Tests
 */

// No declare(strict_types=1): wp-cli eval-file wraps this in eval().

use Registry\PostType\GiftRegistry;

$fails = 0;
$ok    = static function (bool $cond, string $label) use (&$fails): void {
    echo ($cond ? '  ok   ' : '  FAIL ') . $label . "\n";
    if (! $cond) {
        ++$fails;
    }
};

$admin    = get_users(['role' => 'administrator', 'number' => 1])[0];
$customer = wp_insert_user(['user_login' => 'regcap-c-' . wp_generate_password(6, false), 'user_pass' => wp_generate_password(), 'role' => 'customer']);
$editor   = wp_insert_user(['user_login' => 'regcap-e-' . wp_generate_password(6, false), 'user_pass' => wp_generate_password(), 'role' => 'editor']);

$registry = wp_insert_post(['post_type' => GiftRegistry::POST_TYPE, 'post_status' => 'publish', 'post_title' => 'Cap check', 'post_author' => $customer]);

$ok(map_meta_cap('manage_woocommerce', $admin->ID) === ['manage_woocommerce'], 'manage_woocommerce is still a primitive cap');
$ok(user_can($admin, 'manage_woocommerce'), 'admin keeps manage_woocommerce');
$ok(user_can($admin, 'edit_post', $registry), 'admin can edit a customer registry');
$ok(! user_can($editor, 'edit_post', $registry), 'editor cannot edit a customer registry');
$ok(! user_can($editor, 'delete_post', $registry), 'editor cannot delete a customer registry');

wp_delete_post($registry, true);
require_once ABSPATH . 'wp-admin/includes/user.php';
wp_delete_user($customer);
wp_delete_user($editor);

echo $fails === 0 ? "PASS\n" : "FAIL: {$fails}\n";
