<?php

declare(strict_types=1);

namespace Sectionist\Storage;

final class PostContentRepository implements Repository
{
    public function load(int $postId): Document
    {
        return Document::fromJson($postId > 0 ? get_post_field('post_content', $postId, 'raw') : '');
    }

    public function encode(int $postId, Document $document): ?string
    {
        if ($this->load($postId)->isNewerThanSupported()) {
            return null;
        }

        $json = wp_json_encode($document->toArray());

        return $json === false ? null : $json;
    }
}
