<?php

declare(strict_types=1);

namespace Sectionist\Storage;

final class PostMetaRepository implements Repository
{
    public const META_KEY = '_sectionist_data';

    public function load(int $postId): Document
    {
        $raw = get_post_meta($postId, self::META_KEY, true);

        return Document::fromJson(is_string($raw) ? $raw : '');
    }

    public function save(int $postId, Document $document): bool
    {
        if ($this->load($postId)->isNewerThanSupported()) {
            return false;
        }

        $json = wp_json_encode($document->toArray());
        if ($json === false) {
            return false;
        }

        update_post_meta($postId, self::META_KEY, wp_slash($json));

        return true;
    }
}
