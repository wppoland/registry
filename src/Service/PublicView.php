<?php

declare(strict_types=1);

namespace Registry\Service;

use Registry\Contract\HasHooks;
use Registry\PostType\GiftRegistry;
use Registry\Support\Settings;

defined('ABSPATH') || exit;

/**
 * Renders the public, read-only view of a registry — both on the registry's own
 * permalink (via the_content) and through the [gift_registry id="123"] shortcode
 * for embedding in any page.
 *
 * Guests see the event details, each wanted product, how many are still needed
 * (desired minus already-purchased) and a button to buy directly. Fully-fulfilled
 * items are clearly marked so nobody double-buys. All output is escaped; the
 * front-end assets load only on pages that actually render a registry.
 */
final class PublicView implements HasHooks
{
    public function __construct(
        private readonly GiftRegistry $cpt,
        private readonly PurchaseTracker $tracker,
        private readonly Settings $settings,
    ) {
    }

    public function registerHooks(): void
    {
        add_shortcode('gift_registry', [$this, 'shortcode']);
        add_filter('the_content', [$this, 'appendToSinglePermalink']);
        add_action('wp_enqueue_scripts', [$this, 'registerAssets']);
    }

    /**
     * Register (not enqueue) the stylesheet up front. render() enqueues it only
     * when a registry is actually output, including shortcodes mid-content — WP
     * prints styles enqueued during the_loop in the footer.
     */
    public function registerAssets(): void
    {
        wp_register_style(
            'registry',
            REGISTRY_URL . 'assets/css/registry.css',
            [],
            \Registry\VERSION,
        );
    }

    /**
     * On a single registry permalink, render the registry below its title.
     */
    public function appendToSinglePermalink(string $content): string
    {
        if (! is_singular(GiftRegistry::POST_TYPE) || ! in_the_loop() || ! is_main_query()) {
            return $content;
        }

        $registryId = get_the_ID();

        if (false === $registryId) {
            return $content;
        }

        return $content . $this->render((int) $registryId);
    }

    /**
     * [gift_registry id="123"] — embed a registry anywhere.
     *
     * @param array<string, mixed>|string $atts
     */
    public function shortcode(array|string $atts): string
    {
        $atts = shortcode_atts(['id' => 0], (array) $atts, 'gift_registry');
        $id   = absint($atts['id']);

        if ($id <= 0) {
            return '';
        }

        return $this->render($id);
    }

    /**
     * Render a registry's public view. Returns escaped HTML, or a graceful
     * message when the registry is missing/unpublished/disabled.
     */
    public function render(int $registryId): string
    {
        if (! $this->settings->isEnabled()) {
            return '';
        }

        $post = get_post($registryId);

        if (! $post instanceof \WP_Post || GiftRegistry::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status) {
            return $this->notice(__('This gift registry is not available.', 'registry'));
        }

        wp_enqueue_style('registry');

        $items     = $this->cpt->items($registryId);
        $purchased = $this->tracker->purchased($registryId);
        $eventType = (string) get_post_meta($registryId, GiftRegistry::META_EVENT_TYPE, true);
        $eventDate = (string) get_post_meta($registryId, GiftRegistry::META_EVENT_DATE, true);

        ob_start();
        ?>
        <div class="registry-public">
            <header class="registry-public__head">
                <h2 class="registry-public__title"><?php echo esc_html(get_the_title($registryId)); ?></h2>
                <p class="registry-public__meta">
                    <span class="registry-public__badge"><?php echo esc_html(GiftRegistry::eventTypeLabel($eventType)); ?></span>
                    <?php if ('' !== $eventDate) : ?>
                        <span class="registry-public__date">
                            <?php
                            $ts = strtotime($eventDate);
                            echo esc_html(false !== $ts ? wp_date((string) get_option('date_format'), $ts) : $eventDate);
                            ?>
                        </span>
                    <?php endif; ?>
                </p>
                <?php
                $intro = trim((string) $this->settings->get('public_intro', ''));
                if ('' !== $intro) :
                    ?>
                    <div class="registry-public__intro"><?php echo wp_kses_post(wpautop($intro)); ?></div>
                <?php endif; ?>
            </header>

            <?php if ([] === $items) : ?>
                <?php echo $this->notice(__('No items have been added to this registry yet.', 'registry')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- notice() escapes. ?>
            <?php else : ?>
                <ul class="registry-public__items">
                    <?php foreach ($items as $productId => $desired) : ?>
                        <?php
                        $product = wc_get_product($productId);
                        if (! $product instanceof \WC_Product) {
                            continue;
                        }
                        $bought    = $purchased[$productId] ?? 0;
                        $remaining = max(0, $desired - $bought);
                        $fulfilled = $remaining <= 0;
                        ?>
                        <li class="registry-public__item<?php echo $fulfilled ? ' is-fulfilled' : ''; ?>">
                            <div class="registry-public__item-media">
                                <a href="<?php echo esc_url((string) get_permalink($productId)); ?>">
                                    <?php echo wp_kses_post($product->get_image('woocommerce_thumbnail')); ?>
                                </a>
                            </div>
                            <div class="registry-public__item-body">
                                <a class="registry-public__item-name" href="<?php echo esc_url((string) get_permalink($productId)); ?>">
                                    <?php echo esc_html($product->get_name()); ?>
                                </a>
                                <p class="registry-public__item-price"><?php echo wp_kses_post($product->get_price_html()); ?></p>
                                <p class="registry-public__item-progress">
                                    <?php
                                    printf(
                                        /* translators: 1: purchased count, 2: desired count */
                                        esc_html__('%1$d of %2$d purchased', 'registry'),
                                        (int) $bought,
                                        (int) $desired,
                                    );
                                    ?>
                                </p>
                            </div>
                            <div class="registry-public__item-action">
                                <?php echo $this->buyButton($product, $registryId, $remaining); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- buyButton escapes internally. ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Buy button / fulfilled badge for a single registry item.
     */
    private function buyButton(\WC_Product $product, int $registryId, int $remaining): string
    {
        if ($remaining <= 0) {
            return '<span class="registry-public__fulfilled" aria-label="' .
                esc_attr__('Fully purchased', 'registry') . '">' .
                esc_html__('Fully purchased', 'registry') . '</span>';
        }

        if (! $this->settings->allowsPurchase() || ! $product->is_purchasable() || ! $product->is_in_stock()) {
            return '<a class="button" href="' . esc_url((string) get_permalink($product->get_id())) . '">' .
                esc_html__('View product', 'registry') . '</a>';
        }

        $url = add_query_arg(
            [
                'add-to-cart'             => $product->get_id(),
                PurchaseTracker::ITEM_KEY => $registryId,
            ],
            (string) get_permalink($product->get_id()),
        );

        return sprintf(
            '<a class="button registry-public__buy" href="%1$s">%2$s</a>',
            esc_url($url),
            esc_html__('Buy this gift', 'registry'),
        );
    }

    private function notice(string $message): string
    {
        return '<p class="registry-public__notice" role="status">' . esc_html($message) . '</p>';
    }
}
