<?php

declare(strict_types=1);

namespace Registry\Support;

defined('ABSPATH') || exit;

/**
 * Read-only accessor for the merged plugin settings. Centralises the
 * option-name + defaults merge so every service reads settings the same way and
 * always has sane fallbacks even when the option is missing or corrupt.
 */
final class Settings
{
    public const OPTION = 'registry_settings';

    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    /**
     * Return the full merged settings array.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if (null !== $this->cache) {
            return $this->cache;
        }

        $stored = get_option(self::OPTION, []);

        if (! is_array($stored)) {
            $stored = [];
        }

        /** @var array<string, mixed> $defaults */
        $defaults = require REGISTRY_DIR . 'config/defaults.php';

        return $this->cache = array_merge($defaults, $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->get('enabled', false);
    }

    public function allowsPurchase(): bool
    {
        return (bool) $this->get('allow_purchase', true);
    }

    /**
     * Drop the in-request cache (used after a save in the same request).
     */
    public function flush(): void
    {
        $this->cache = null;
    }
}
