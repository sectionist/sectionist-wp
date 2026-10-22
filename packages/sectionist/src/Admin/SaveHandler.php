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
    ) {}

    public function register(): void
    {
        add_filter('wp_insert_post_data', [$this, 'filterPostData'], 10, 2);
        add_action('admin_notices', [$this, 'showError']);
    }

    public function showError(): void
    {
        $key = self::errorKey();
        if (get_transient($key) === false) {
            return;
        }

        delete_transient($key);
        echo '<div class="notice notice-error"><p>'
            .esc_html__('Sectionist: the sections could not be saved. Your previous sections were kept. Please reload the page and try again.', 'sectionist')
            .'</p></div>';
    }

    /**
     * Writes the sections into `post_content` of our post types. Runs after core
     * sanitizing (kses), so the JSON stays intact. Without a valid request the
     * stored content is kept, because the default editor field is not submitted.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $postarr
     * @return array<string, mixed>
     */
    public function filterPostData(array $data, array $postarr): array
    {
        if (! in_array($data['post_type'] ?? '', MetaBox::postTypes(), true)) {
            return $data;
        }

        $postId = (int) ($postarr['ID'] ?? 0);
        $autosave = defined('DOING_AUTOSAVE') && DOING_AUTOSAVE;
        $json = $this->encode($postId, $_POST, $autosave);

        if ($json !== null) {
            $data['post_content'] = wp_slash($json);

            return $data;
        }

        $fromForm = isset($_POST[self::DATA_FIELD]);
        if (! $autosave && $fromForm) {
            set_transient(self::errorKey(), 1, 60);
        }

        $stored = $postId > 0 ? (string) get_post_field('post_content', $postId, 'raw') : '';
        $incoming = wp_unslash((string) ($data['post_content'] ?? ''));
        if (! $fromForm && $this->isAcceptableExternalWrite($incoming, $stored)) {
            return $data;
        }

        $data['post_content'] = wp_slash($stored);

        return $data;
    }

    /**
     * Writes from outside the editor (REST, import, wp_update_post, other plugins)
     * are only accepted when they are a valid Sectionist document and the stored
     * document is not newer than this code supports.
     */
    private function isAcceptableExternalWrite(string $incoming, string $stored): bool
    {
        $decoded = json_decode($incoming, true);
        if (! is_array($decoded) || ! is_array($decoded['sections'] ?? null)) {
            return false;
        }

        $version = $decoded['schema_version'] ?? Document::CURRENT_VERSION;
        if (! is_int($version) || $version > Document::CURRENT_VERSION) {
            return false;
        }

        return ! Document::fromJson($stored)->isNewerThanSupported();
    }

    /**
     * Validates the request and sanitizes every section with its type. Returns
     * the JSON to store, or null when nothing may be written.
     *
     * @param  array<mixed>  $request
     */
    public function encode(int $postId, array $request, bool $autosave = false): ?string
    {
        if ($autosave) {
            return null;
        }

        $nonce = $request[self::NONCE_FIELD] ?? null;
        if (! is_string($nonce) || wp_verify_nonce($nonce, self::NONCE_ACTION) === false) {
            return null;
        }

        if (! current_user_can('edit_post', $postId)) {
            return null;
        }

        $raw = $request[self::DATA_FIELD] ?? null;
        if (! is_string($raw)) {
            return null;
        }

        $decoded = json_decode(wp_unslash($raw), true);
        if (! is_array($decoded) || ! is_array($decoded['sections'] ?? null)) {
            return null;
        }

        return $this->repository->encode(
            $postId,
            new Document($this->registry->sanitizeSections($decoded['sections'])),
        );
    }

    private static function errorKey(): string
    {
        return 'sectionist_save_error_'.get_current_user_id();
    }
}
