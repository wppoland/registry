<?php

declare(strict_types=1);

namespace Registry\Service;

use Registry\Contract\HasHooks;
use Registry\PostType\GiftRegistry;
use Registry\Support\Settings;

defined('ABSPATH') || exit;

/**
 * Exposes gift registries through the WordPress Abilities API (WP 6.9+).
 *
 * Each ability is a named, schema-described contract the command palette, an MCP
 * server or an AI assistant can call to do exactly what a customer does by hand
 * under My Account: read a registry, see how much of each gift guests have
 * already bought, and manage the wanted items.
 *
 * Nothing here talks to the database on its own. Every ability delegates to
 * RegistryManager, GiftRegistry and PurchaseTracker, so registry ownership and
 * the purchased-quantity map stay the single source of truth.
 *
 * The service stays completely inert on WordPress below 6.9, where the Abilities
 * API does not exist, and when registries are switched off in the settings.
 *
 * Categories:
 *   - registry-registries: the lists themselves, their event details and totals.
 *   - registry-items: the products on a list and their desired quantities.
 */
final class AbilitiesService implements HasHooks
{
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

        if (! function_exists('wp_register_ability')) {
            // WordPress below 6.9, where the Abilities API does not exist. No-op.
            return;
        }

        add_action('wp_abilities_api_categories_init', [$this, 'registerCategories']);
        add_action('wp_abilities_api_init', [$this, 'registerAbilities']);
    }

    public function registerCategories(): void
    {
        if (! function_exists('wp_register_ability_category')) {
            return;
        }

        wp_register_ability_category('registry-registries', [
            'label'       => __('Registry, gift registries', 'plogins-registry'),
            'description' => __('Gift registries a customer owns, with their event details and shareable link.', 'plogins-registry'),
        ]);

        wp_register_ability_category('registry-items', [
            'label'       => __('Registry, registry items', 'plogins-registry'),
            'description' => __('The products wanted on a registry, their desired quantities and what guests have already bought.', 'plogins-registry'),
        ]);
    }

    public function registerAbilities(): void
    {
        if (! function_exists('wp_register_ability')) {
            return;
        }

        $this->registerListRegistries();
        $this->registerGetRegistry();
    }

    private function registerListRegistries(): void
    {
        wp_register_ability('registry/list-registries', [
            'label'               => __('List a customer\'s gift registries', 'plogins-registry'),
            'description'         => __('Returns the gift registries owned by a customer, with the event details, the shareable link and how many wanted items are still unbought.', 'plogins-registry'),
            'category'            => 'registry-registries',
            'input_schema'        => [
                'type'       => 'object',
                'required'   => ['customer_id'],
                'properties' => [
                    'customer_id' => ['type' => 'integer', 'minimum' => 1],
                ],
            ],
            'output_schema'       => [
                'type'       => 'object',
                'properties' => [
                    'registries' => [
                        'type'  => 'array',
                        'items' => [
                            'type'       => 'object',
                            'properties' => [
                                'registry_id'            => ['type' => 'integer'],
                                'title'                  => ['type' => 'string'],
                                'event_type'             => ['type' => 'string'],
                                'event_type_label'       => ['type' => 'string'],
                                'event_date'             => ['type' => 'string'],
                                'share_url'              => ['type' => 'string'],
                                'item_count'             => ['type' => 'integer'],
                                'outstanding_item_count' => ['type' => 'integer'],
                            ],
                        ],
                    ],
                ],
            ],
            'execute_callback'    => [$this, 'executeListRegistries'],
            'permission_callback' => [$this, 'canReadCustomer'],
            'meta'                => ['show_in_rest' => true, 'readonly' => true],
        ]);
    }

    private function registerGetRegistry(): void
    {
        wp_register_ability('registry/get-registry', [
            'label'               => __('Get a gift registry with its items', 'plogins-registry'),
            'description'         => __('Returns one registry: event details, shareable link, and every wanted product with the quantity wanted, the quantity already bought by guests and the quantity still needed.', 'plogins-registry'),
            'category'            => 'registry-registries',
            'input_schema'        => [
                'type'       => 'object',
                'required'   => ['registry_id'],
                'properties' => [
                    'registry_id' => ['type' => 'integer', 'minimum' => 1],
                ],
            ],
            'output_schema'       => [
                'type'       => 'object',
                'properties' => [
                    'registry_id'      => ['type' => 'integer'],
                    'title'            => ['type' => 'string'],
                    'status'           => ['type' => 'string'],
                    'owner_id'         => ['type' => 'integer'],
                    'event_type'       => ['type' => 'string'],
                    'event_type_label' => ['type' => 'string'],
                    'event_date'       => ['type' => 'string'],
                    'share_url'        => ['type' => 'string'],
                    'items'            => [
                        'type'  => 'array',
                        'items' => [
                            'type'       => 'object',
                            'properties' => [
                                'product_id' => ['type' => 'integer'],
                                'name'       => ['type' => 'string'],
                                'sku'        => ['type' => 'string'],
                                'permalink'  => ['type' => 'string'],
                                'wanted'     => ['type' => 'integer'],
                                'purchased'  => ['type' => 'integer'],
                                'remaining'  => ['type' => 'integer'],
                                'fulfilled'  => ['type' => 'boolean'],
                            ],
                        ],
                    ],
                ],
            ],
            'execute_callback'    => [$this, 'executeGetRegistry'],
            'permission_callback' => [$this, 'canReadRegistry'],
            'meta'                => ['show_in_rest' => true, 'readonly' => true],
        ]);
    }





    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function executeListRegistries(array $input): array
    {
        $customerId = absint($input['customer_id'] ?? 0);
        $rows       = [];

        foreach ($this->manager->forUser($customerId) as $registry) {
            $registryId  = (int) $registry->ID;
            $items       = $this->cpt->items($registryId);
            $outstanding = 0;

            foreach ($items as $productId => $desired) {
                if ($this->tracker->remaining($registryId, $productId, $desired) > 0) {
                    ++$outstanding;
                }
            }

            $eventType = (string) get_post_meta($registryId, GiftRegistry::META_EVENT_TYPE, true);

            $rows[] = [
                'registry_id'            => $registryId,
                'title'                  => (string) get_the_title($registry),
                'event_type'             => $eventType,
                'event_type_label'       => GiftRegistry::eventTypeLabel($eventType),
                'event_date'             => (string) get_post_meta($registryId, GiftRegistry::META_EVENT_DATE, true),
                'share_url'              => (string) get_permalink($registryId),
                'item_count'             => count($items),
                'outstanding_item_count' => $outstanding,
            ];
        }

        return ['registries' => $rows];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>|\WP_Error
     */
    public function executeGetRegistry(array $input): array|\WP_Error
    {
        $registryId = absint($input['registry_id'] ?? 0);
        $post       = get_post($registryId);

        if (! $post instanceof \WP_Post || GiftRegistry::POST_TYPE !== $post->post_type) {
            return new \WP_Error(
                'registry_not_found',
                __('No gift registry was found for that ID.', 'plogins-registry'),
                ['status' => 404],
            );
        }

        $eventType = (string) get_post_meta($registryId, GiftRegistry::META_EVENT_TYPE, true);

        return [
            'registry_id'      => $registryId,
            'title'            => (string) get_the_title($post),
            // A registry the customer deleted is trashed, not erased, so the
            // status travels with the payload instead of being assumed live.
            'status'           => (string) $post->post_status,
            'owner_id'         => (int) $post->post_author,
            'event_type'       => $eventType,
            'event_type_label' => GiftRegistry::eventTypeLabel($eventType),
            'event_date'       => (string) get_post_meta($registryId, GiftRegistry::META_EVENT_DATE, true),
            'share_url'        => (string) get_permalink($registryId),
            'items'            => $this->itemRows($registryId),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, bool>
     */
    public function executeUpdateDetails(array $input): array
    {
        $registryId = absint($input['registry_id'] ?? 0);

        // updateDetails() always writes the event type and date, so anything the
        // caller left out is read back from the registry first. Without that, an
        // agent renaming a list would quietly reset the event to "other".
        $title = isset($input['title'])
            ? sanitize_text_field((string) $input['title'])
            : (string) get_the_title($registryId);

        $eventType = isset($input['event_type'])
            ? sanitize_key((string) $input['event_type'])
            : (string) get_post_meta($registryId, GiftRegistry::META_EVENT_TYPE, true);

        $eventDate = isset($input['event_date'])
            ? sanitize_text_field((string) $input['event_date'])
            : (string) get_post_meta($registryId, GiftRegistry::META_EVENT_DATE, true);

        return [
            'ok' => $this->manager->updateDetails(
                $registryId,
                $this->actingUserId($registryId),
                $title,
                $eventType,
                $eventDate,
            ),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, bool>
     */
    public function executeAddItem(array $input): array
    {
        $registryId = absint($input['registry_id'] ?? 0);

        return [
            'ok' => $this->manager->addItem(
                $registryId,
                $this->actingUserId($registryId),
                absint($input['product_id'] ?? 0),
                max(1, absint($input['quantity'] ?? 1)),
            ),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, bool>
     */
    public function executeSetItemQuantity(array $input): array
    {
        $registryId = absint($input['registry_id'] ?? 0);

        return [
            'ok' => $this->manager->setQuantity(
                $registryId,
                $this->actingUserId($registryId),
                absint($input['product_id'] ?? 0),
                absint($input['quantity'] ?? 0),
            ),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, bool>
     */
    public function executeRemoveItem(array $input): array
    {
        $registryId = absint($input['registry_id'] ?? 0);

        return [
            'ok' => $this->manager->removeItem(
                $registryId,
                $this->actingUserId($registryId),
                absint($input['product_id'] ?? 0),
            ),
        ];
    }

    /**
     * Shop managers may read any customer's registries; a customer may only ask
     * for their own.
     *
     * The Abilities API also checks permissions without input, when it lists what
     * the current user may call, so every permission callback here takes mixed
     * and copes with nothing being passed.
     */
    public function canReadCustomer(mixed $input = null): bool
    {
        if (current_user_can('manage_woocommerce')) {
            return true;
        }

        $customerId = is_array($input) ? absint($input['customer_id'] ?? 0) : 0;

        return $customerId > 0 && $customerId === get_current_user_id();
    }

    /**
     * Shop managers may read any registry; everyone else needs the plugin's own
     * management right on it (the owner, plus whoever registry/can_manage adds).
     */
    public function canReadRegistry(mixed $input = null): bool
    {
        return $this->canManageRegistry($input);
    }

    public function canManageRegistry(mixed $input = null): bool
    {
        if (current_user_can('manage_woocommerce')) {
            return true;
        }

        $registryId = is_array($input) ? absint($input['registry_id'] ?? 0) : 0;

        return $registryId > 0 && $this->cpt->canManage($registryId, get_current_user_id());
    }

    /**
     * The user RegistryManager should act as.
     *
     * RegistryManager guards every write with registry ownership, not with a
     * WordPress capability, so a shop manager helping a customer acts as that
     * registry's owner, the same way they would edit the customer's list over
     * the phone. Everyone else acts as themselves and the ownership rule applies
     * unchanged.
     */
    private function actingUserId(int $registryId): int
    {
        $userId = get_current_user_id();

        if ($this->cpt->canManage($registryId, $userId) || ! current_user_can('manage_woocommerce')) {
            return $userId;
        }

        $post = get_post($registryId);

        return $post instanceof \WP_Post && GiftRegistry::POST_TYPE === $post->post_type
            ? (int) $post->post_author
            : $userId;
    }

    /**
     * Wanted / bought / still needed for every product on a registry, built from
     * the same item map and purchase counts the public share page renders.
     *
     * @return array<int, array<string, mixed>>
     */
    private function itemRows(int $registryId): array
    {
        $rows      = [];
        $purchased = $this->tracker->purchased($registryId);

        foreach ($this->cpt->items($registryId) as $productId => $desired) {
            $product = wc_get_product($productId);

            if (! $product instanceof \WC_Product) {
                continue;
            }

            $remaining = $this->tracker->remaining($registryId, $productId, $desired);

            $rows[] = [
                'product_id' => $productId,
                'name'       => $product->get_name(),
                'sku'        => $product->get_sku(),
                'permalink'  => (string) get_permalink($productId),
                'wanted'     => $desired,
                'purchased'  => $purchased[$productId] ?? 0,
                'remaining'  => $remaining,
                'fulfilled'  => $remaining <= 0,
            ];
        }

        return $rows;
    }
}
