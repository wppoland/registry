<?php

declare(strict_types=1);

namespace Registry\Admin;

defined('ABSPATH') || exit;

use Registry\Contract\HasHooks;
use Registry\Support\Settings as SettingsStore;

/**
 * Admin management page registered as a WooCommerce submenu.
 *
 * Holds the master toggle and the "allow direct purchase" switch. Everything is
 * stored in the `registry_settings` option (array); all output is escaped and
 * all input sanitised on save. Capabilities are gated to manage_woocommerce.
 */
final class Settings implements HasHooks
{
    private const PAGE  = 'registry-settings';
    private const GROUP = 'registry_settings_group';

    public function __construct(private readonly SettingsStore $settings)
    {
    }

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function enqueueAssets(string $hook): void
    {
        if ('woocommerce_page_' . self::PAGE !== $hook) {
            return;
        }

        wp_enqueue_style(
            'registry-admin',
            REGISTRY_URL . 'assets/css/admin.css',
            [],
            \Registry\VERSION,
        );
    }

    public function addMenuPage(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Gift Registries', 'registry'),
            __('Gift Registries', 'registry'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'renderPage'],
        );
    }

    public function registerSettings(): void
    {
        register_setting(
            self::GROUP,
            SettingsStore::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
            ],
        );

        add_filter(
            'option_page_capability_' . self::GROUP,
            static fn (): string => 'manage_woocommerce',
        );
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }
        ?>
        <div class="wrap registry-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="registry-intro">
                <h2><?php esc_html_e('Gift registries for your store', 'registry'); ?></h2>
                <p><?php esc_html_e('Let logged-in customers build shareable gift registries for weddings, baby showers and other events. Guests open the shared link, see what is still needed, and buy directly — purchased quantities are tracked from orders so nobody double-buys.', 'registry'); ?></p>
                <p><?php esc_html_e('Customers manage their registries under My Account → Gift Registries.', 'registry'); ?></p>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields(self::GROUP); ?>

                <div class="registry-card">
                    <h2><?php esc_html_e('General', 'registry'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><?php esc_html_e('Enable gift registries', 'registry'); ?></th>
                                <td>
                                    <label for="registry_enabled">
                                        <input type="checkbox" id="registry_enabled"
                                            name="<?php echo esc_attr(SettingsStore::OPTION); ?>[enabled]" value="1"
                                            <?php checked($this->settings->isEnabled(), true); ?> />
                                        <?php esc_html_e('Show gift registries on the storefront.', 'registry'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('The master switch. When off, the storefront button, the My Account area and public registry pages are not rendered.', 'registry'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Allow direct purchase', 'registry'); ?></th>
                                <td>
                                    <label for="registry_allow_purchase">
                                        <input type="checkbox" id="registry_allow_purchase"
                                            name="<?php echo esc_attr(SettingsStore::OPTION); ?>[allow_purchase]" value="1"
                                            <?php checked($this->settings->allowsPurchase(), true); ?> />
                                        <?php esc_html_e('Let guests buy registry items directly from the shared page.', 'registry'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('When off, guests are sent to the product page instead.', 'registry'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * @param mixed $raw
     * @return array<string, mixed>
     */
    public function sanitize(mixed $raw): array
    {
        if (! is_array($raw)) {
            $raw = [];
        }

        return [
            'enabled'        => ! empty($raw['enabled']),
            'allow_purchase' => ! empty($raw['allow_purchase']),
        ];
    }
}
