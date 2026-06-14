<?php

declare(strict_types=1);

namespace Registry\Service;

use Registry\PostType\GiftRegistry;

defined('ABSPATH') || exit;

/**
 * Owner-side operations on a registry: create, rename, set event details, add and
 * remove items and adjust desired quantities. Every mutating method enforces
 * ownership through GiftRegistry::isOwner() so a logged-in user can never act on
 * a registry that is not theirs (no IDOR).
 *
 * These methods are intentionally side-effect light: they validate, persist and
 * return a boolean / id. Controllers (My Account, storefront button) handle
 * nonces, sanitisation of raw request input and redirects.
 */
final class RegistryManager
{
    public function __construct(private readonly GiftRegistry $cpt)
    {
    }

    /**
     * Create a registry owned by the given user. Returns the new post ID, or 0
     * on failure / invalid input.
     */
    public function create(int $userId, string $title, string $eventType, string $eventDate): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $title = trim($title);

        if ('' === $title) {
            $title = __('My gift registry', 'registry');
        }

        $postId = wp_insert_post(
            [
                'post_type'   => GiftRegistry::POST_TYPE,
                'post_status' => 'publish',
                'post_title'  => $title,
                'post_author' => $userId,
            ],
            true,
        );

        if (is_wp_error($postId) || 0 === $postId) {
            return 0;
        }

        $postId = (int) $postId;

        update_post_meta($postId, GiftRegistry::META_EVENT_TYPE, $this->normaliseEventType($eventType));
        update_post_meta($postId, GiftRegistry::META_EVENT_DATE, $this->normaliseDate($eventDate));

        return $postId;
    }

    /**
     * Update the title and event details of a registry the user owns.
     */
    public function updateDetails(int $registryId, int $userId, string $title, string $eventType, string $eventDate): bool
    {
        if (! $this->cpt->isOwner($registryId, $userId)) {
            return false;
        }

        $title = trim($title);

        if ('' !== $title) {
            $result = wp_update_post(
                ['ID' => $registryId, 'post_title' => $title],
                true,
            );

            if (is_wp_error($result)) {
                return false;
            }
        }

        update_post_meta($registryId, GiftRegistry::META_EVENT_TYPE, $this->normaliseEventType($eventType));
        update_post_meta($registryId, GiftRegistry::META_EVENT_DATE, $this->normaliseDate($eventDate));

        return true;
    }

    /**
     * Delete (trash) a registry the user owns.
     */
    public function delete(int $registryId, int $userId): bool
    {
        if (! $this->cpt->isOwner($registryId, $userId)) {
            return false;
        }

        return null !== wp_trash_post($registryId);
    }

    /**
     * Add a product to a registry (or bump its desired quantity), owner only.
     */
    public function addItem(int $registryId, int $userId, int $productId, int $qty = 1): bool
    {
        if (! $this->cpt->isOwner($registryId, $userId)) {
            return false;
        }

        $product = wc_get_product($productId);

        if (! $product instanceof \WC_Product || ! $product->is_purchasable()) {
            return false;
        }

        $qty   = max(1, $qty);
        $items = $this->cpt->items($registryId);

        $items[$productId] = isset($items[$productId])
            ? $items[$productId] + $qty
            : $qty;

        $this->cpt->saveItems($registryId, $items);

        return true;
    }

    /**
     * Set the desired quantity of a single item, owner only. A quantity of 0
     * removes the item.
     */
    public function setQuantity(int $registryId, int $userId, int $productId, int $qty): bool
    {
        if (! $this->cpt->isOwner($registryId, $userId)) {
            return false;
        }

        $items = $this->cpt->items($registryId);

        if ($qty <= 0) {
            unset($items[$productId]);
        } else {
            if (! isset($items[$productId])) {
                return false;
            }
            $items[$productId] = $qty;
        }

        $this->cpt->saveItems($registryId, $items);

        return true;
    }

    /**
     * Remove an item from a registry, owner only.
     */
    public function removeItem(int $registryId, int $userId, int $productId): bool
    {
        if (! $this->cpt->isOwner($registryId, $userId)) {
            return false;
        }

        $items = $this->cpt->items($registryId);

        if (! isset($items[$productId])) {
            return false;
        }

        unset($items[$productId]);
        $this->cpt->saveItems($registryId, $items);

        return true;
    }

    /**
     * All registries owned by a user, newest first.
     *
     * @return array<int, \WP_Post>
     */
    public function forUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        return get_posts([
            'post_type'      => GiftRegistry::POST_TYPE,
            'author'         => $userId,
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
    }

    private function normaliseEventType(string $eventType): string
    {
        $eventType = sanitize_key($eventType);

        return array_key_exists($eventType, GiftRegistry::eventTypes()) ? $eventType : 'other';
    }

    private function normaliseDate(string $date): string
    {
        $date = trim($date);

        if ('' === $date) {
            return '';
        }

        $ts = strtotime($date);

        return false === $ts ? '' : gmdate('Y-m-d', $ts);
    }
}
