<?php

declare(strict_types=1);

namespace Sectionist\Storage;

interface Repository
{
    public function load(int $postId): Document;

    /**
     * Returns false when nothing was written, e.g. because the stored data
     * has a newer schema version than this code supports.
     */
    public function save(int $postId, Document $document): bool;
}
