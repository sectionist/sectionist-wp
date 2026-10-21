<?php

declare(strict_types=1);

namespace Sectionist\Admin;

use Sectionist\Sections\Registry;
use Sectionist\Storage\Document;
use Sectionist\Storage\Repository;

final class SaveHandler
{
    public const NONCE_ACTION = 'sectionist_save';

    public const NONCE_FIELD = 'sectionist_nonce';

    public const DATA_FIELD = 'sectionist_data';

    public function __construct(
        private readonly Registry $registry,
        private readonly Repository $repository,
    ) {
    }

    public function register(): void
    {
        add_action('save_post', [$this, 'handle'], 10, 1);
    }

    public function handle(int $postId): void
    {
        $autosave = (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($postId) !== false;

        $this->process($postId, $_POST, $autosave);
    }

    /**
     * Validates the request, sanitizes every section with its type and stores
     * the document. Returns false when nothing was written.
     *
     * @param array<mixed> $request
     */
    public function process(int $postId, array $request, bool $autosave = false): bool
    {
        if ($autosave) {
            return false;
        }

        $nonce = $request[self::NONCE_FIELD] ?? null;
        if (! is_string($nonce) || wp_verify_nonce($nonce, self::NONCE_ACTION) === false) {
            return false;
        }

        if (! current_user_can('edit_post', $postId)) {
            return false;
        }

        $raw = $request[self::DATA_FIELD] ?? null;
        if (! is_string($raw)) {
            return false;
        }

        $decoded = json_decode(wp_unslash($raw), true);
        if (! is_array($decoded) || ! is_array($decoded['sections'] ?? null)) {
            return false;
        }

        return $this->repository->save(
            $postId,
            new Document($this->registry->sanitizeSections($decoded['sections'])),
        );
    }
}
