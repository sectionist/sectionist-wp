<?php

declare(strict_types=1);

namespace Sectionist\Render;

use Sectionist\Admin\MetaBox;
use Sectionist\Storage\Document;
use Sectionist\Storage\Repository;

/**
 * Derives a plain-text excerpt from the sections, so lists, archives and feeds
 * do not show the stored JSON. A manually written WordPress excerpt always wins.
 */
final readonly class Excerpt
{
    public function __construct(private Repository $repository) {}

    public function register(): void
    {
        add_filter('get_the_excerpt', [$this, 'filter'], 20, 2);
    }

    public function filter(mixed $excerpt, mixed $post = null): mixed
    {
        if (! is_object($post) || ! isset($post->ID, $post->post_type, $post->post_excerpt)
            || ! in_array($post->post_type, MetaBox::postTypes(), true)
            || (string) $post->post_excerpt !== '') {
            return $excerpt;
        }

        $text = self::text($this->repository->load((int) $post->ID));

        return wp_trim_words($text, 55, '&hellip;');
    }

    /**
     * Plain text of all rich text fields of the active sections, in editor order.
     */
    public static function text(Document $document): string
    {
        $parts = [];
        foreach ($document->activeSections() as $section) {
            $body = $section->data['body'] ?? null;
            if (is_string($body) && $body !== '') {
                $parts[] = wp_strip_all_tags($body);
            }
        }

        return trim((string) preg_replace('/\s+/', ' ', implode(' ', $parts)));
    }
}
