<?php

declare(strict_types=1);

namespace Registry\Service;

use Registry\Contract\HasHooks;
use Registry\Support\Settings;

defined('ABSPATH') || exit;

/**
 * Storefront "Add to registry" affordance.
 *
 * On single product pages, logged-in customers who own at least one registry see
 * a control to add the current product to one of their registries. The action is
 * nonce-protected and ownership-checked in RegistryManager. A real form submit
 * works without JavaScript; the request is handled on template_redirect before
 * any output so it can redirect cleanly.
 */
final class AddToRegistry implements HasHooks
{
    private const NONCE = 'registry_add_item';

    public function __construct(
        private readonly RegistryManager $manager,
        private readonly Settings $settings,
    ) {
    }

    public function registerHooks(): void
    {
        if (! $this->settings->isEnabled()) {
            return;
        }

        add_action('woocommerce_after_add_to_cart_button', [$this, 'renderControl']);
        add_action('template_redirect', [$this, 'handleSubmit']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(): void
    {
        if (! is_product()) {
            return;
        }

        wp_enqueue_style(
            'registry',
            REGISTRY_URL . 'assets/css/registry.css',
            [],
            \Registry\VERSION,
        );
    }

    /**
     * Render the add-to-registry control under the add-to-cart button.
     */
    public function renderControl(): void
    {
        if (! is_user_logged_in()) {
            return;
        }

        global $product;

        if (! $product instanceof \WC_Product || ! $product->is_purchasable()) {
            return;
        }

        $userId     = get_current_user_id();
        $registries = $this->manager->forUser($userId);

        $myAccount = wc_get_account_endpoint_url('registries');
        ?>
        <div class="registry-add">
            <?php if ([] === $registries) : ?>
                <p class="registry-add__empty">
                    <a href="<?php echo esc_url($myAccount); ?>"><?php esc_html_e('Create a gift registry', 'registry'); ?></a>
                    <?php esc_html_e('to add this product.', 'registry'); ?>
                </p>
            <?php else : ?>
                <form method="post" class="registry-add__form">
                    <?php wp_nonce_field(self::NONCE, 'registry_add_nonce'); ?>
                    <input type="hidden" name="registry_add_product" value="<?php echo esc_attr((string) $product->get_id()); ?>" />
                    <label class="registry-add__label" for="registry-add-select">
                        <?php esc_html_e('Add to gift registry', 'registry'); ?>
                    </label>
                    <span class="registry-add__row">
                        <select id="registry-add-select" name="registry_add_id" class="registry-add__select">
                            <?php foreach ($registries as $registry) : ?>
                                <option value="<?php echo esc_attr((string) $registry->ID); ?>">
                                    <?php echo esc_html(get_the_title($registry)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button registry-add__button">
                            <?php echo esc_html($this->buttonLabel()); ?>
                        </button>
                    </span>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Handle the no-JS add-to-registry form submission.
     */
    public function handleSubmit(): void
    {
        if (! isset($_POST['registry_add_id'], $_POST['registry_add_product'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            return;
        }

        $nonce = isset($_POST['registry_add_nonce'])
            ? sanitize_text_field(wp_unslash($_POST['registry_add_nonce']))
            : '';

        if (! wp_verify_nonce($nonce, self::NONCE) || ! is_user_logged_in()) {
            return;
        }

        $registryId = absint(wp_unslash($_POST['registry_add_id']));
        $productId  = absint(wp_unslash($_POST['registry_add_product']));
        $userId     = get_current_user_id();

        $added = $this->manager->addItem($registryId, $userId, $productId);

        $redirect = add_query_arg(
            'registry_added',
            $added ? '1' : '0',
            (string) get_permalink($productId),
        );

        wp_safe_redirect($redirect);
        exit;
    }

    private function buttonLabel(): string
    {
        $custom = trim((string) $this->settings->get('button_text', ''));

        return '' !== $custom ? $custom : __('Add to registry', 'registry');
    }
}
