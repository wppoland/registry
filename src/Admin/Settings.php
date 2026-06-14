<?php

declare(strict_types=1);

namespace Registry\Admin;

defined('ABSPATH') || exit;

use Registry\Contract\HasHooks;
use Registry\PostType\GiftRegistry;
use Registry\Support\Settings as SettingsStore;

/**
 * Admin management page registered as a WooCommerce submenu.
 *
 * Holds the master toggle, storefront button label, optional public-page intro
 * and the "allow direct purchase" switch, plus a read-only overview of the most
 * recent registries on the site. Everything is stored in the
 * `registry_settings` option (array); all output is escaped and all input
 * sanitised on save. Capabilities are gated to manage_woocommerce.
 */
final class Settings implements HasHooks
{
    private const PAGE  = 'registry-settings';
    private const GROUP = 'registry_settings_group';

    /** Incremented to give each inline-help control a unique id/anchor. */
    private int $helpSeq = 0;

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

        $settings = $this->settings->all();
        ?>
        <div class="wrap registry-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="registry-intro">
                <h2><?php esc_html_e('Gift registries for your store', 'registry'); ?></h2>
                <p><?php esc_html_e('Let logged-in customers build shareable gift registries for weddings, baby showers and other events. Guests open the shared link, see what is still needed, and buy directly — purchased quantities are tracked from orders so nobody double-buys.', 'registry'); ?></p>
                <p>
                    <?php
                    printf(
                        /* translators: %s: shortcode wrapped in <code>. */
                        esc_html__('Customers manage their registries under My Account → Gift Registries. You can also embed any registry with the %s shortcode.', 'registry'),
                        '<code>[gift_registry id="123"]</code>',
                    );
                    ?>
                </p>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields(self::GROUP); ?>

                <div class="registry-card">
                    <h2><?php esc_html_e('General', 'registry'); ?></h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Enable gift registries', 'registry'); ?>
                                    <?php $this->help(__('The master switch. When off, the storefront button, the My Account area and public registry pages are not rendered.', 'registry')); ?>
                                </th>
                                <td>
                                    <label for="registry_enabled">
                                        <input type="checkbox" id="registry_enabled"
                                            name="<?php echo esc_attr(SettingsStore::OPTION); ?>[enabled]" value="1"
                                            <?php checked($this->settings->isEnabled(), true); ?> />
                                        <?php esc_html_e('Show gift registries on the storefront.', 'registry'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Allow direct purchase', 'registry'); ?>
                                    <?php $this->help(__('When on, guests can buy a registry item straight from the public page. When off, they are sent to the product page instead.', 'registry')); ?>
                                </th>
                                <td>
                                    <label for="registry_allow_purchase">
                                        <input type="checkbox" id="registry_allow_purchase"
                                            name="<?php echo esc_attr(SettingsStore::OPTION); ?>[allow_purchase]" value="1"
                                            <?php checked($this->settings->allowsPurchase(), true); ?> />
                                        <?php esc_html_e('Let guests buy registry items directly from the shared page.', 'registry'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="registry_button_text"><?php esc_html_e('Button text', 'registry'); ?></label>
                                    <?php $this->help(__('The label on the "Add to registry" button shown under the add-to-cart button. Leave blank to use the default.', 'registry')); ?>
                                </th>
                                <td>
                                    <input type="text" id="registry_button_text" class="regular-text"
                                        name="<?php echo esc_attr(SettingsStore::OPTION); ?>[button_text]"
                                        value="<?php echo esc_attr((string) $settings['button_text']); ?>"
                                        placeholder="<?php esc_attr_e('Add to registry', 'registry'); ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="registry_public_intro"><?php esc_html_e('Public page intro', 'registry'); ?></label>
                                    <?php $this->help(__('Optional text shown above every public registry. Basic HTML is allowed.', 'registry')); ?>
                                </th>
                                <td>
                                    <textarea id="registry_public_intro" class="large-text" rows="3"
                                        name="<?php echo esc_attr(SettingsStore::OPTION); ?>[public_intro]"><?php
                                        echo esc_textarea((string) $settings['public_intro']);
                                    ?></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>

            <?php $this->renderRecent(); ?>
        </div>
        <?php
    }

    /**
     * Read-only list of the most recent registries on the site.
     */
    private function renderRecent(): void
    {
        $recent = get_posts([
            'post_type'      => GiftRegistry::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
        ?>
        <div class="registry-card">
            <h2><?php esc_html_e('Recent registries', 'registry'); ?></h2>
            <?php if ([] === $recent) : ?>
                <p><?php esc_html_e('No registries have been created yet.', 'registry'); ?></p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Registry', 'registry'); ?></th>
                            <th><?php esc_html_e('Owner', 'registry'); ?></th>
                            <th><?php esc_html_e('Event', 'registry'); ?></th>
                            <th><?php esc_html_e('Created', 'registry'); ?></th>
                            <th><?php esc_html_e('Link', 'registry'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $registry) : ?>
                            <?php
                            $owner     = get_userdata((int) $registry->post_author);
                            $eventType = (string) get_post_meta($registry->ID, GiftRegistry::META_EVENT_TYPE, true);
                            ?>
                            <tr>
                                <td><?php echo esc_html(get_the_title($registry)); ?></td>
                                <td><?php echo esc_html($owner instanceof \WP_User ? $owner->display_name : '—'); ?></td>
                                <td><?php echo esc_html(GiftRegistry::eventTypeLabel($eventType)); ?></td>
                                <td><?php echo esc_html(get_the_date('', $registry)); ?></td>
                                <td>
                                    <a href="<?php echo esc_url((string) get_permalink($registry)); ?>" target="_blank" rel="noopener">
                                        <?php esc_html_e('View', 'registry'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Accessible inline-help affordance using the native Popover API.
     */
    private function help(string $text): void
    {
        $id = 'registry-help-' . (++$this->helpSeq);
        ?>
        <button type="button" class="registry-help"
            aria-label="<?php esc_attr_e('More information', 'registry'); ?>"
            aria-describedby="<?php echo esc_attr($id); ?>"
            popovertarget="<?php echo esc_attr($id); ?>">?</button>
        <div id="<?php echo esc_attr($id); ?>" class="registry-tip" role="tooltip" popover hidden>
            <?php echo esc_html($text); ?>
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
            'button_text'    => isset($raw['button_text']) ? sanitize_text_field((string) $raw['button_text']) : '',
            'public_intro'   => isset($raw['public_intro']) ? wp_kses_post((string) $raw['public_intro']) : '',
        ];
    }
}
