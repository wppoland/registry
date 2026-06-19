<?php

declare(strict_types=1);

namespace Registry\Account;

use Registry\Contract\HasHooks;
use Registry\PostType\GiftRegistry;
use Registry\Service\PurchaseTracker;
use Registry\Service\RegistryManager;
use Registry\Support\Settings;

defined('ABSPATH') || exit;

/**
 * The "My Registries" area in WooCommerce → My Account.
 *
 * Registers a `registries` account endpoint that lets a logged-in customer list
 * their registries, create a new one, edit event details, manage desired
 * quantities, remove items, copy the shareable link and delete a registry.
 *
 * Every mutating request is nonce-verified and the ownership of the target
 * registry is checked in RegistryManager before anything is changed (no IDOR).
 * All output is escaped and all input sanitised.
 */
final class MyRegistries implements HasHooks
{
    public const ENDPOINT = 'registries';

    private const NONCE = 'registry_account';

    /** @var array<int, string> */
    private array $notices = [];

    public function __construct(
        private readonly RegistryManager $manager,
        private readonly GiftRegistry $cpt,
        private readonly PurchaseTracker $tracker,
        private readonly Settings $settings,
    ) {
    }

    public function registerHooks(): void
    {
        if (! $this->settings->isEnabled()) {
            return;
        }

        add_action('init', [$this, 'addEndpoint']);
        add_filter('woocommerce_account_menu_items', [$this, 'addMenuItem']);
        add_action('woocommerce_account_' . self::ENDPOINT . '_endpoint', [$this, 'renderEndpoint']);
        add_action('template_redirect', [$this, 'handleActions']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    public function addEndpoint(): void
    {
        add_rewrite_endpoint(self::ENDPOINT, EP_ROOT | EP_PAGES);
    }

    /**
     * Insert the menu item before "Logout".
     *
     * @param array<string, string> $items
     * @return array<string, string>
     */
    public function addMenuItem(array $items): array
    {
        $reordered = [];

        foreach ($items as $key => $label) {
            if ('customer-logout' === $key) {
                $reordered[self::ENDPOINT] = __('Gift Registries', 'registry');
            }
            $reordered[$key] = $label;
        }

        if (! isset($reordered[self::ENDPOINT])) {
            $reordered[self::ENDPOINT] = __('Gift Registries', 'registry');
        }

        return $reordered;
    }

    public function enqueue(): void
    {
        if (! is_account_page()) {
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
     * Handle create / update / delete / item actions before output, so we can
     * redirect cleanly and avoid resubmission.
     */
    public function handleActions(): void
    {
        if (! isset($_POST['registry_action']) || ! is_user_logged_in()) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            return;
        }

        $nonce = isset($_POST['registry_nonce'])
            ? sanitize_text_field(wp_unslash($_POST['registry_nonce']))
            : '';

        if (! wp_verify_nonce($nonce, self::NONCE)) {
            return;
        }

        // The nonce is verified above, so all request reads in this scope are safe.
        // Collect and sanitise every field once, then hand typed data to handlers.
        $action = sanitize_key(wp_unslash($_POST['registry_action']));
        $userId = get_current_user_id();

        $data = [
            'id'         => isset($_POST['registry_id']) ? absint(wp_unslash($_POST['registry_id'])) : 0,
            'title'      => isset($_POST['registry_title']) ? sanitize_text_field(wp_unslash($_POST['registry_title'])) : '',
            'event_type' => isset($_POST['registry_event_type']) ? sanitize_key(wp_unslash($_POST['registry_event_type'])) : 'other',
            'event_date' => isset($_POST['registry_event_date']) ? sanitize_text_field(wp_unslash($_POST['registry_event_date'])) : '',
            'remove'     => isset($_POST['registry_remove']) ? absint(wp_unslash($_POST['registry_remove'])) : 0,
            // Each value is cast to int in sanitiseQtyMap(); nonce verified above.
            'qty'        => $this->sanitiseQtyMap(isset($_POST['qty']) && is_array($_POST['qty']) ? wp_unslash($_POST['qty']) : []), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        ];

        switch ($action) {
            case 'create':
                $this->doCreate($userId, $data);
                break;
            case 'update':
                $this->doUpdate($userId, $data);
                break;
            case 'delete':
                $this->doDelete($userId, $data);
                break;
            case 'items':
                $this->doItems($userId, $data);
                break;
        }
    }

    /**
     * Coerce a raw qty[] map into product_id => quantity ints.
     *
     * @param array<int|string, mixed> $raw
     * @return array<int, int>
     */
    private function sanitiseQtyMap(array $raw): array
    {
        $clean = [];

        foreach ($raw as $productId => $qty) {
            $clean[absint($productId)] = absint($qty);
        }

        return $clean;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function doCreate(int $userId, array $data): void
    {
        $limit = (int) apply_filters('registry/max_registries_limit', 1, $userId);
        $registries = $this->manager->forUser($userId);

        if ($limit > 0 && count($registries) >= $limit) {
            $this->redirect(['msg' => 'limit_reached']);
            return;
        }

        $id = $this->manager->create(
            $userId,
            (string) $data['title'],
            (string) $data['event_type'],
            (string) $data['event_date'],
        );

        $this->redirect($id > 0 ? ['view' => $id, 'msg' => 'created'] : ['msg' => 'error']);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function doUpdate(int $userId, array $data): void
    {
        $id = (int) $data['id'];

        $ok = $this->manager->updateDetails(
            $id,
            $userId,
            (string) $data['title'],
            (string) $data['event_type'],
            (string) $data['event_date'],
        );

        $this->redirect($ok ? ['view' => $id, 'msg' => 'updated'] : ['msg' => 'error']);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function doDelete(int $userId, array $data): void
    {
        $ok = $this->manager->delete((int) $data['id'], $userId);

        $this->redirect(['msg' => $ok ? 'deleted' : 'error']);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function doItems(int $userId, array $data): void
    {
        $id = (int) $data['id'];

        if ($data['remove'] > 0) {
            $this->manager->removeItem($id, $userId, (int) $data['remove']);
            $this->redirect(['view' => $id, 'msg' => 'updated']);
            return;
        }

        /** @var array<int, int> $qtys */
        $qtys = $data['qty'];
        foreach ($qtys as $productId => $qty) {
            $this->manager->setQuantity($id, $userId, $productId, $qty);
        }

        $this->redirect(['view' => $id, 'msg' => 'updated']);
    }

    /**
     * @param array<string, int|string> $args
     */
    private function redirect(array $args): void
    {
        $base = wc_get_account_endpoint_url(self::ENDPOINT);
        wp_safe_redirect(add_query_arg($args, $base));
        exit;
    }

    /**
     * Render the endpoint: either a single registry manager or the list.
     */
    public function renderEndpoint(): void
    {
        $this->collectNotice();

        // Read-only navigation flag; mutations are POST + nonce verified elsewhere.
        $viewId = isset($_GET['view']) ? absint(wp_unslash($_GET['view'])) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $userId = get_current_user_id();

        echo '<div class="registry-account">';
        $this->renderNotices();

        if ($viewId > 0 && $this->cpt->isOwner($viewId, $userId)) {
            $this->renderSingle($viewId);
        } else {
            $this->renderList($userId);
        }

        echo '</div>';
    }

    private function collectNotice(): void
    {
        // Read-only feedback flag set by our own redirects.
        if (! isset($_GET['msg'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $map = [
            'created'       => __('Registry created.', 'registry'),
            'updated'       => __('Registry updated.', 'registry'),
            'deleted'       => __('Registry deleted.', 'registry'),
            'limit_reached' => __('You have reached the maximum number of gift registries allowed.', 'registry'),
            'error'         => __('Sorry, that action could not be completed.', 'registry'),
        ];

        $key = sanitize_key(wp_unslash($_GET['msg'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if (isset($map[$key])) {
            $this->notices[] = $map[$key];
        }
    }

    private function renderNotices(): void
    {
        foreach ($this->notices as $notice) {
            printf(
                '<div class="woocommerce-message" role="status">%s</div>',
                esc_html($notice),
            );
        }
    }

    private function renderList(int $userId): void
    {
        $limit      = (int) apply_filters('registry/max_registries_limit', 1, $userId);
        $registries = $this->manager->forUser($userId);
        ?>
        <p class="registry-account__intro">
            <?php esc_html_e('Create a gift registry, add the products you would love to receive, and share the link with friends and family.', 'registry'); ?>
        </p>

        <?php if ([] === $registries) : ?>
            <p><?php esc_html_e('You have not created any registries yet.', 'registry'); ?></p>
        <?php else : ?>
            <table class="registry-account__table shop_table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Registry', 'registry'); ?></th>
                        <th><?php esc_html_e('Event', 'registry'); ?></th>
                        <th><?php esc_html_e('Items', 'registry'); ?></th>
                        <th><span class="screen-reader-text"><?php esc_html_e('Actions', 'registry'); ?></span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registries as $registry) : ?>
                        <?php
                        $manageUrl = add_query_arg('view', $registry->ID, wc_get_account_endpoint_url(self::ENDPOINT));
                        $eventType = (string) get_post_meta($registry->ID, GiftRegistry::META_EVENT_TYPE, true);
                        $itemCount = count($this->cpt->items((int) $registry->ID));
                        ?>
                        <tr>
                            <td><a href="<?php echo esc_url($manageUrl); ?>"><?php echo esc_html(get_the_title($registry)); ?></a></td>
                            <td><?php echo esc_html(GiftRegistry::eventTypeLabel($eventType)); ?></td>
                            <td><?php echo esc_html((string) $itemCount); ?></td>
                            <td><a class="button" href="<?php echo esc_url($manageUrl); ?>"><?php esc_html_e('Manage', 'registry'); ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($limit <= 0 || count($registries) < $limit) : ?>
            <h3><?php esc_html_e('Create a new registry', 'registry'); ?></h3>
            <?php $this->renderDetailsForm('create', 0, '', 'wedding', ''); ?>
        <?php else : ?>
            <div class="woocommerce-info">
                <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML from filter is safe.
                echo apply_filters(
                    'registry/limit_notice_html',
                    sprintf(
                        /* translators: %d: registries limit */
                        __('You have reached the limit of %d gift registry. Upgrade to Registry Pro to create multiple gift registries.', 'registry'),
                        $limit
                    )
                );
                ?>
            </div>
        <?php endif; ?>
        <?php
    }

    private function renderSingle(int $registryId): void
    {
        $title     = get_the_title($registryId);
        $eventType = (string) get_post_meta($registryId, GiftRegistry::META_EVENT_TYPE, true);
        $eventDate = (string) get_post_meta($registryId, GiftRegistry::META_EVENT_DATE, true);
        $items     = $this->cpt->items($registryId);
        $purchased = $this->tracker->purchased($registryId);
        $shareUrl  = (string) get_permalink($registryId);
        $listUrl   = wc_get_account_endpoint_url(self::ENDPOINT);
        ?>
        <p><a href="<?php echo esc_url($listUrl); ?>">&larr; <?php esc_html_e('Back to all registries', 'registry'); ?></a></p>

        <h3><?php echo esc_html($title); ?></h3>

        <p class="registry-account__share">
            <label for="registry-share-url"><?php esc_html_e('Shareable link:', 'registry'); ?></label>
            <input type="url" id="registry-share-url" class="registry-account__share-input" readonly
                value="<?php echo esc_url($shareUrl); ?>"
                onfocus="this.select()" />
            <a class="button" href="<?php echo esc_url($shareUrl); ?>" target="_blank" rel="noopener">
                <?php esc_html_e('Open', 'registry'); ?>
            </a>
        </p>

        <h4><?php esc_html_e('Event details', 'registry'); ?></h4>
        <?php $this->renderDetailsForm('update', $registryId, $title, $eventType, $eventDate); ?>

        <h4><?php esc_html_e('Items', 'registry'); ?></h4>
        <?php if ([] === $items) : ?>
            <p><?php esc_html_e('No items yet. Browse the shop and use "Add to registry" on a product.', 'registry'); ?></p>
        <?php else : ?>
            <form method="post" class="registry-account__items-form">
                <?php wp_nonce_field(self::NONCE, 'registry_nonce'); ?>
                <input type="hidden" name="registry_action" value="items" />
                <input type="hidden" name="registry_id" value="<?php echo esc_attr((string) $registryId); ?>" />
                <table class="registry-account__table shop_table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Product', 'registry'); ?></th>
                            <th><?php esc_html_e('Wanted', 'registry'); ?></th>
                            <th><?php esc_html_e('Purchased', 'registry'); ?></th>
                            <th><span class="screen-reader-text"><?php esc_html_e('Remove', 'registry'); ?></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $productId => $desired) : ?>
                            <?php
                            $product = wc_get_product($productId);
                            if (! $product instanceof \WC_Product) {
                                continue;
                            }
                            $bought = $purchased[$productId] ?? 0;
                            ?>
                            <tr>
                                <td data-label="<?php esc_attr_e('Product', 'registry'); ?>">
                                    <?php echo esc_html($product->get_name()); ?>
                                </td>
                                <td data-label="<?php esc_attr_e('Wanted', 'registry'); ?>">
                                    <label class="screen-reader-text" for="registry-qty-<?php echo esc_attr((string) $productId); ?>">
                                        <?php esc_html_e('Desired quantity', 'registry'); ?>
                                    </label>
                                    <input type="number" min="1" step="1"
                                        id="registry-qty-<?php echo esc_attr((string) $productId); ?>"
                                        name="qty[<?php echo esc_attr((string) $productId); ?>]"
                                        value="<?php echo esc_attr((string) $desired); ?>"
                                        class="registry-account__qty" />
                                </td>
                                <td data-label="<?php esc_attr_e('Purchased', 'registry'); ?>"><?php echo esc_html((string) $bought); ?></td>
                                <td>
                                    <button type="submit" name="registry_remove" value="<?php echo esc_attr((string) $productId); ?>"
                                        class="registry-account__remove" formnovalidate
                                        aria-label="<?php
                                        /* translators: %s: product name */
                                        echo esc_attr(sprintf(__('Remove %s', 'registry'), $product->get_name()));
                                        ?>"><span aria-hidden="true">&times;</span></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><button type="submit" class="button"><?php esc_html_e('Update quantities', 'registry'); ?></button></p>
            </form>
        <?php endif; ?>

        <h4><?php esc_html_e('Delete registry', 'registry'); ?></h4>
        <form method="post" class="registry-account__delete-form"
            onsubmit="return confirm('<?php echo esc_js(__('Delete this registry? This cannot be undone.', 'registry')); ?>');">
            <?php wp_nonce_field(self::NONCE, 'registry_nonce'); ?>
            <input type="hidden" name="registry_action" value="delete" />
            <input type="hidden" name="registry_id" value="<?php echo esc_attr((string) $registryId); ?>" />
            <button type="submit" class="button registry-account__delete"><?php esc_html_e('Delete registry', 'registry'); ?></button>
        </form>
        <?php
    }

    /**
     * Shared create/update details form.
     */
    private function renderDetailsForm(string $mode, int $registryId, string $title, string $eventType, string $eventDate): void
    {
        ?>
        <form method="post" class="registry-account__details-form">
            <?php wp_nonce_field(self::NONCE, 'registry_nonce'); ?>
            <input type="hidden" name="registry_action" value="<?php echo esc_attr($mode); ?>" />
            <?php if ($registryId > 0) : ?>
                <input type="hidden" name="registry_id" value="<?php echo esc_attr((string) $registryId); ?>" />
            <?php endif; ?>

            <p class="registry-account__field">
                <label for="registry-title-<?php echo esc_attr($mode); ?>"><?php esc_html_e('Registry name', 'registry'); ?></label>
                <input type="text" id="registry-title-<?php echo esc_attr($mode); ?>" name="registry_title"
                    value="<?php echo esc_attr($title); ?>" required
                    placeholder="<?php esc_attr_e('e.g. Anna & Tom’s Wedding', 'registry'); ?>" />
            </p>

            <p class="registry-account__field">
                <label for="registry-type-<?php echo esc_attr($mode); ?>"><?php esc_html_e('Event type', 'registry'); ?></label>
                <select id="registry-type-<?php echo esc_attr($mode); ?>" name="registry_event_type">
                    <?php foreach (GiftRegistry::eventTypes() as $slug => $label) : ?>
                        <option value="<?php echo esc_attr($slug); ?>" <?php selected($eventType, $slug); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p class="registry-account__field">
                <label for="registry-date-<?php echo esc_attr($mode); ?>"><?php esc_html_e('Event date', 'registry'); ?></label>
                <input type="date" id="registry-date-<?php echo esc_attr($mode); ?>" name="registry_event_date"
                    value="<?php echo esc_attr($eventDate); ?>" />
            </p>

            <p>
                <button type="submit" class="button">
                    <?php echo 'create' === $mode ? esc_html__('Create registry', 'registry') : esc_html__('Save details', 'registry'); ?>
                </button>
            </p>
        </form>
        <?php
    }
}
