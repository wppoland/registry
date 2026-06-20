<?php

declare(strict_types=1);

namespace Registry\PostType;

use Registry\Contract\HasHooks;

defined('ABSPATH') || exit;

/**
 * The `gift_registry` custom post type.
 *
 * A registry is owned by the customer who created it (post_author) and is
 * publicly readable via a clean permalink so it can be shared with guests, but
 * it is not editable from the front end except by its owner through the plugin's
 * own controlled flows. Items and event metadata live in post meta. The CPT is
 * deliberately kept out of the public archive/search listings — registries are
 * only reachable by their direct, shareable link.
 */
final class GiftRegistry implements HasHooks
{
    public const POST_TYPE = 'gift_registry';

    /** Event type slug (wedding, baby, birthday, …). */
    public const META_EVENT_TYPE = '_registry_event_type';

    /** Event date as Y-m-d. */
    public const META_EVENT_DATE = '_registry_event_date';

    /**
     * Items as an ordered list of product_id => desired quantity.
     *
     * @var string
     */
    public const META_ITEMS = '_registry_items';

    /**
     * Co-owner / contributor user IDs (array of ints). Written by Registry Pro
     * when group gifting is enabled; the free plugin exposes the meta key and
     * read helper only.
     *
     * @var string
     */
    public const META_CONTRIBUTORS = '_registry_contributors';

    public function registerHooks(): void
    {
        $this->register();
    }

    /**
     * Register the post type. Called directly during boot on init so rewrite
     * rules and the public permalink are available immediately.
     */
    public function register(): void
    {
        if (post_type_exists(self::POST_TYPE)) {
            return;
        }

        register_post_type(
            self::POST_TYPE,
            [
                'labels'              => [
                    'name'          => __('Gift Registries', 'registry'),
                    'singular_name' => __('Gift Registry', 'registry'),
                    'menu_name'     => __('Gift Registries', 'registry'),
                    'all_items'     => __('Gift Registries', 'registry'),
                    'edit_item'     => __('View Gift Registry', 'registry'),
                    'view_item'     => __('View Gift Registry', 'registry'),
                    'search_items'  => __('Search gift registries', 'registry'),
                    'not_found'     => __('No gift registries found.', 'registry'),
                ],
                // Publicly readable (shareable link) but not listed/searchable.
                'public'              => true,
                'show_ui'             => false,
                'show_in_menu'        => false,
                'show_in_nav_menus'   => false,
                'show_in_rest'        => false,
                'exclude_from_search' => true,
                'publicly_queryable'  => true,
                'has_archive'         => false,
                'hierarchical'        => false,
                'rewrite'             => ['slug' => 'gift-registry', 'with_front' => false],
                'query_var'           => true,
                'menu_icon'           => 'dashicons-heart',
                'supports'            => ['title', 'author'],
                'capability_type'     => 'post',
                'map_meta_cap'        => true,
            ],
        );
    }

    /**
     * Read the stored desired-quantity item map for a registry.
     *
     * @return array<int, int> product_id => desired quantity
     */
    public function items(int $registryId): array
    {
        $raw = get_post_meta($registryId, self::META_ITEMS, true);

        if (! is_array($raw)) {
            return [];
        }

        $clean = [];

        foreach ($raw as $productId => $qty) {
            $productId = absint($productId);
            $qty       = absint($qty);

            if ($productId > 0 && $qty > 0) {
                $clean[$productId] = $qty;
            }
        }

        return $clean;
    }

    /**
     * Persist the item map for a registry.
     *
     * @param array<int, int> $items
     */
    public function saveItems(int $registryId, array $items): void
    {
        $clean = [];

        foreach ($items as $productId => $qty) {
            $productId = absint($productId);
            $qty       = absint($qty);

            if ($productId > 0 && $qty > 0) {
                $clean[$productId] = $qty;
            }
        }

        update_post_meta($registryId, self::META_ITEMS, $clean);
    }

    /**
     * Whether the given user is the primary owner (post author). Extensions can
     * widen management access via the registry/can_manage filter without
     * changing this check.
     */
    public function isOwner(int $registryId, int $userId): bool
    {
        if ($registryId <= 0 || $userId <= 0) {
            return false;
        }

        $post = get_post($registryId);

        if (! $post instanceof \WP_Post || self::POST_TYPE !== $post->post_type) {
            return false;
        }

        $isAuthor = (int) $post->post_author === $userId;

        /**
         * Filter whether the user is the primary registry owner.
         *
         * @param bool $isAuthor   Whether the user is the post author.
         * @param int  $registryId Registry post ID.
         * @param int  $userId     User ID.
         */
        return (bool) apply_filters('registry/is_owner', $isAuthor, $registryId, $userId);
    }

    /**
     * Whether the user may manage registry details and items (not necessarily
     * delete). Defaults to primary ownership; PRO group gifting extends this
     * via registry/can_manage.
     */
    public function canManage(int $registryId, int $userId): bool
    {
        $can = $this->isOwner($registryId, $userId);

        /**
         * Filter whether the user may manage a registry (edit details, items).
         *
         * @param bool $can        Default access (primary owner).
         * @param int  $registryId Registry post ID.
         * @param int  $userId     User ID.
         */
        return (bool) apply_filters('registry/can_manage', $can, $registryId, $userId);
    }

    /**
     * Whether the user may delete (trash) the registry. Defaults to primary
     * ownership so co-owners added by PRO cannot remove the list itself.
     */
    public function canDelete(int $registryId, int $userId): bool
    {
        $can = $this->isOwner($registryId, $userId);

        /**
         * Filter whether the user may delete a registry.
         *
         * @param bool $can        Default access (primary owner).
         * @param int  $registryId Registry post ID.
         * @param int  $userId     User ID.
         */
        return (bool) apply_filters('registry/can_delete', $can, $registryId, $userId);
    }

    /**
     * Contributor user IDs stored on the registry (empty when none).
     *
     * @return array<int, int>
     */
    public function contributors(int $registryId): array
    {
        $raw = get_post_meta($registryId, self::META_CONTRIBUTORS, true);

        if (! is_array($raw)) {
            return [];
        }

        $clean = [];

        foreach ($raw as $userId) {
            $userId = absint($userId);

            if ($userId > 0) {
                $clean[] = $userId;
            }
        }

        return array_values(array_unique($clean));
    }

    /**
     * Human label for an event type slug.
     */
    public static function eventTypeLabel(string $slug): string
    {
        $types = self::eventTypes();

        return $types[$slug] ?? $types['other'];
    }

    /**
     * The supported event types as slug => label.
     *
     * @return array<string, string>
     */
    public static function eventTypes(): array
    {
        return [
            'wedding'   => __('Wedding', 'registry'),
            'baby'      => __('Baby shower', 'registry'),
            'birthday'  => __('Birthday', 'registry'),
            'housewarming' => __('Housewarming', 'registry'),
            'other'     => __('Other', 'registry'),
        ];
    }
}
