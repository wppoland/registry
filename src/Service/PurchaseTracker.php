<?php

declare(strict_types=1);

namespace Registry\Service;

use Registry\Contract\HasHooks;
use Registry\PostType\GiftRegistry;

defined('ABSPATH') || exit;

/**
 * Tracks how many of each registry item have already been purchased, so the
 * public registry page can show remaining quantities and guests do not
 * double-buy.
 *
 * When a guest adds a registry item to the cart from a public registry page the
 * registry ID is carried as cart item data, persisted onto the order line item,
 * and — once the order reaches a paid status — folded into a per-registry
 * purchased-quantity map stored as post meta. The map is keyed by product ID so
 * lookups on the public page are O(1) with no extra queries.
 */
final class PurchaseTracker implements HasHooks
{
    /** Cart/line-item key carrying the registry id. */
    public const ITEM_KEY = '_registry_id';

    /** Post meta: product_id => purchased quantity. */
    public const META_PURCHASED = '_registry_purchased';

    /** Order meta flag so we only count each order once (idempotency). */
    private const ORDER_COUNTED = '_registry_counted';

    public function registerHooks(): void
    {
        // Carry registry context from the add-to-cart request into cart item data.
        add_filter('woocommerce_add_cart_item_data', [$this, 'captureCartItemData'], 10, 3);

        // Persist it onto the order line item at checkout.
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'saveToLineItem'], 10, 4);

        // Count purchases when an order becomes paid / processing / completed.
        add_action('woocommerce_order_status_processing', [$this, 'countOrder']);
        add_action('woocommerce_order_status_completed', [$this, 'countOrder']);
        add_action('woocommerce_payment_complete', [$this, 'countOrder']);
    }

    /**
     * Attach the registry id (if supplied and valid) to the cart item.
     *
     * @param array<string, mixed> $cartItemData
     * @return array<string, mixed>
     */
    public function captureCartItemData(array $cartItemData, int $productId, int $variationId): array
    {
        unset($variationId);

        // Read-only context flag from the add-to-cart link; the cart token guards the action.
        if (! isset($_REQUEST[self::ITEM_KEY])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return $cartItemData;
        }

        $registryId = absint(wp_unslash($_REQUEST[self::ITEM_KEY])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if ($registryId <= 0) {
            return $cartItemData;
        }

        $registry = get_post($registryId);

        if (! $registry instanceof \WP_Post || GiftRegistry::POST_TYPE !== $registry->post_type) {
            return $cartItemData;
        }

        $cartItemData[self::ITEM_KEY] = $registryId;

        return $cartItemData;
    }

    /**
     * Copy the registry id from cart item data onto the order line item.
     *
     * @param array<string, mixed> $values
     */
    public function saveToLineItem(\WC_Order_Item_Product $item, string $cartItemKey, array $values, \WC_Order $order): void
    {
        unset($cartItemKey, $order);

        if (empty($values[self::ITEM_KEY])) {
            return;
        }

        $item->add_meta_data(self::ITEM_KEY, (string) absint($values[self::ITEM_KEY]), true);
    }

    /**
     * Fold an order's registry line items into the per-registry purchased map.
     * Idempotent: an order is only ever counted once.
     */
    public function countOrder(int $orderId): void
    {
        $order = wc_get_order($orderId);

        if (! $order instanceof \WC_Order) {
            return;
        }

        if ('' !== (string) $order->get_meta(self::ORDER_COUNTED)) {
            return;
        }

        $touched = [];

        foreach ($order->get_items() as $item) {
            if (! $item instanceof \WC_Order_Item_Product) {
                continue;
            }

            $registryId = absint($item->get_meta(self::ITEM_KEY));

            if ($registryId <= 0) {
                continue;
            }

            $productId = $item->get_product_id();
            $qty       = (int) $item->get_quantity();

            if ($productId <= 0 || $qty <= 0) {
                continue;
            }

            $touched[$registryId] ??= [];
            $touched[$registryId][$productId] = ($touched[$registryId][$productId] ?? 0) + $qty;
        }

        foreach ($touched as $registryId => $products) {
            $this->increment($registryId, $products);
        }

        $order->update_meta_data(self::ORDER_COUNTED, '1');
        $order->save();
    }

    /**
     * Purchased quantities for a registry, product_id => qty.
     *
     * @return array<int, int>
     */
    public function purchased(int $registryId): array
    {
        $raw = get_post_meta($registryId, self::META_PURCHASED, true);

        if (! is_array($raw)) {
            return [];
        }

        $clean = [];

        foreach ($raw as $productId => $qty) {
            $clean[absint($productId)] = absint($qty);
        }

        return $clean;
    }

    /**
     * Remaining quantity still needed for a single product in a registry.
     */
    public function remaining(int $registryId, int $productId, int $desired): int
    {
        $purchased = $this->purchased($registryId)[$productId] ?? 0;

        return max(0, $desired - $purchased);
    }

    /**
     * @param array<int, int> $products product_id => qty bought in this order
     */
    private function increment(int $registryId, array $products): void
    {
        $current = $this->purchased($registryId);

        foreach ($products as $productId => $qty) {
            $current[$productId] = ($current[$productId] ?? 0) + $qty;
        }

        update_post_meta($registryId, self::META_PURCHASED, $current);
    }
}
