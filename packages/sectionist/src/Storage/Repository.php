<?php

declare(strict_types=1);

namespace Sectionist\Storage;

interface Repository
{
    public function load(int $postId): Document;

    /**
     * Returns the JSON to store as `post_content`, or null when it must not be
     * written, e.g. because the stored data has a newer schema version than
     * this code supports. Writing happens in the `wp_insert_post_data` filter.
     */
    public function encode(int $postId, Document $document): ?string;
}
