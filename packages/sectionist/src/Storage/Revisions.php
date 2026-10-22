<?php

declare(strict_types=1);

namespace Sectionist\Storage;

/**
 * Copies and restores the sections with post revisions.
 * Requires WordPress 6.4 or newer (filter `wp_post_revision_meta_keys`).
 */
final class Revisions
{
    public function register(): void
    {
        add_filter('wp_post_revision_meta_keys', $this->keys(...), 10, 1);
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<int, string>
     */
    public function keys(array $keys): array
    {
        return [...$keys, PostMetaRepository::META_KEY];
    }
}
