<?php

declare(strict_types=1);

namespace Registry\Service;

use Registry\Contract\HasHooks;
use Registry\PostType\GiftRegistry;
use WP_Query;
use WP_User;

defined('ABSPATH') || exit;

/**
 * Personal data exporter and eraser for gift registries.
 */
final class RegistryPrivacyService implements HasHooks
{
    private const PAGE_SIZE = 100;

    public function registerHooks(): void
    {
        add_filter('wp_privacy_personal_data_exporters', [$this, 'registerExporters']);
        add_filter('wp_privacy_personal_data_erasers', [$this, 'registerErasers']);
    }

    /**
     * @param array<string, array<string, mixed>> $exporters
     * @return array<string, array<string, mixed>>
     */
    public function registerExporters(array $exporters): array
    {
        $exporters['gift-registries'] = [
            'exporter_friendly_name' => __('Gift Registries', 'plogins-registry'),
            'callback'               => [$this, 'exportRegistries'],
        ];

        return $exporters;
    }

    /**
     * @param array<string, array<string, mixed>> $erasers
     * @return array<string, array<string, mixed>>
     */
    public function registerErasers(array $erasers): array
    {
        $erasers['gift-registries'] = [
            'eraser_friendly_name' => __('Gift Registries', 'plogins-registry'),
            'callback'             => [$this, 'eraseRegistries'],
        ];

        return $erasers;
    }

    /**
     * @return array{data: list<array<string, mixed>>, done: bool}
     */
    public function exportRegistries(string $email, int $page = 1): array
    {
        $user = get_user_by('email', $email);
        if (! $user instanceof WP_User) {
            return ['data' => [], 'done' => true];
        }

        $page    = max(1, $page);
        $postIds = $this->findRegistryPostIds((int) $user->ID, $page);

        $items = [];
        foreach ($postIds as $postId) {
            $post = get_post($postId);
            if (! $post instanceof \WP_Post) {
                continue;
            }

            $eventType = (string) get_post_meta($postId, GiftRegistry::META_EVENT_TYPE, true);
            $eventDate = (string) get_post_meta($postId, GiftRegistry::META_EVENT_DATE, true);
            $rawItems  = get_post_meta($postId, GiftRegistry::META_ITEMS, true);
            $itemCount = is_array($rawItems) ? count($rawItems) : 0;
            $date      = (string) get_the_date('Y-m-d H:i:s', $postId);

            $items[] = [
                'group_id'    => 'gift-registries',
                'group_label' => __('Gift Registries', 'plogins-registry'),
                'item_id'     => 'registry-' . $postId,
                'data'        => [
                    ['name' => __('Registry Title', 'plogins-registry'), 'value' => $post->post_title],
                    ['name' => __('Event Type', 'plogins-registry'), 'value' => $eventType],
                    ['name' => __('Event Date', 'plogins-registry'), 'value' => $eventDate],
                    ['name' => __('Items Count', 'plogins-registry'), 'value' => (string) $itemCount],
                    ['name' => __('Created At', 'plogins-registry'), 'value' => $date],
                ],
            ];
        }

        return [
            'data' => $items,
            'done' => count($postIds) < self::PAGE_SIZE,
        ];
    }

    /**
     * @return array{items_removed: int, items_retained: int, messages: list<string>, done: bool}
     */
    public function eraseRegistries(string $email, int $page = 1): array
    {
        $user = get_user_by('email', $email);
        if (! $user instanceof WP_User) {
            return [
                'items_removed'  => 0,
                'items_retained' => 0,
                'messages'       => [],
                'done'           => true,
            ];
        }

        $page    = max(1, $page);
        $postIds = $this->findRegistryPostIds((int) $user->ID, $page);

        $removed = 0;
        foreach ($postIds as $postId) {
            $deleted = wp_delete_post($postId, true);
            if ($deleted instanceof \WP_Post) {
                $removed++;
            }
        }

        return [
            'items_removed'  => $removed,
            'items_retained' => 0,
            'messages'       => [],
            'done'           => count($postIds) < self::PAGE_SIZE,
        ];
    }

    /**
     * @return list<int>
     */
    private function findRegistryPostIds(int $userId, int $page): array
    {
        $query = new WP_Query([
            'post_type'      => GiftRegistry::POST_TYPE,
            'post_status'    => 'any',
            'author'         => $userId,
            'posts_per_page' => self::PAGE_SIZE,
            'paged'          => $page,
            'fields'         => 'ids',
        ]);

        /** @var list<int> $posts */
        $posts = is_array($query->posts) ? array_map('intval', $query->posts) : [];

        return $posts;
    }
}
