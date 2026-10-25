<?php

declare(strict_types=1);

namespace Sectionist\Faq;

/**
 * Central FAQ: one post per question, grouped by topics. Sections only store a selection.
 */
final class Faq
{
    public const POST_TYPE = 'sectionist_faq';

    public const TAXONOMY = 'faq_topic';

    public const MODE_TOPICS = 'topics';

    public const MODE_SELECTED = 'selected';

    /** Maximum number of topics or entries a section can reference. */
    public const LIMIT = 100;

    public const NONCE_ACTION = 'sectionist_faq_answer';

    public const NONCE_FIELD = 'sectionist_faq_nonce';

    public const ANSWER_FIELD = 'sectionist_faq_answer';

    public static function register(): void
    {
        // Classic editor: a title input for the question. The answer uses the Sectionist rich text editor.
        add_filter('wp_insert_post_data', [self::class, 'filterPostData'], 10, 2);
        add_filter('use_block_editor_for_post_type', static fn (bool $use, string $postType): bool => $postType === self::POST_TYPE ? false : $use, 10, 2);
        add_filter('enter_title_here', static fn (string $title, \WP_Post $post): string => $post->post_type === self::POST_TYPE ? __('Enter question', 'sectionist') : $title, 10, 2);
        add_action('init', static function (): void {
            register_post_type(self::POST_TYPE, [
                'labels' => [
                    'name' => __('FAQ', 'sectionist'),
                    'singular_name' => __('Question', 'sectionist'),
                    'add_new_item' => __('Add question', 'sectionist'),
                    'edit_item' => __('Edit question', 'sectionist'),
                    'menu_name' => __('FAQ', 'sectionist'),
                ],
                'public' => false,
                'show_ui' => true,
                'show_in_rest' => true,
                'rest_base' => self::POST_TYPE,
                'supports' => ['title', 'page-attributes'],
                'menu_icon' => 'dashicons-editor-help',
            ]);
            register_taxonomy(self::TAXONOMY, self::POST_TYPE, [
                'labels' => [
                    'name' => __('Topics', 'sectionist'),
                    'singular_name' => __('Topic', 'sectionist'),
                ],
                'public' => false,
                'show_ui' => true,
                'show_in_rest' => true,
                'rest_base' => self::TAXONOMY,
                'hierarchical' => true,
                'show_admin_column' => true,
            ]);
        });
    }

    /**
     * Stores the answer written in the Sectionist rich text editor as `post_content`.
     * Without a valid request the stored content is kept.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $postarr
     * @return array<string, mixed>
     */
    public static function filterPostData(array $data, array $postarr): array
    {
        if (($data['post_type'] ?? '') !== self::POST_TYPE) {
            return $data;
        }

        $nonce = $_POST[self::NONCE_FIELD] ?? null;
        $answer = $_POST[self::ANSWER_FIELD] ?? null;
        $postId = (int) ($postarr['ID'] ?? 0);
        $autosave = defined('DOING_AUTOSAVE') && DOING_AUTOSAVE;

        if ($autosave || ! is_string($nonce) || ! is_string($answer)
            || wp_verify_nonce($nonce, self::NONCE_ACTION) === false
            || ! current_user_can('edit_post', $postId)) {
            $data['post_content'] = wp_slash($postId > 0 ? (string) get_post_field('post_content', $postId, 'raw') : '');

            return $data;
        }

        $data['post_content'] = wp_slash(wp_kses_post(wp_unslash($answer)));

        return $data;
    }

    /**
     * @return array{mode: string, topics: list<int>, entries: list<int>, semantic: bool}
     */
    public static function defaults(): array
    {
        return ['mode' => self::MODE_TOPICS, 'topics' => [], 'entries' => [], 'semantic' => true];
    }

    /**
     * @return array{mode: string, topics: list<int>, entries: list<int>, semantic: bool}
     */
    public static function sanitize(mixed $value): array
    {
        $value = is_array($value) ? $value : [];
        $mode = ($value['mode'] ?? '') === self::MODE_SELECTED ? self::MODE_SELECTED : self::MODE_TOPICS;

        $topics = array_values(array_filter(
            self::ids($value['topics'] ?? null),
            static fn (int $id): bool => term_exists($id, self::TAXONOMY) !== null,
        ));
        $entries = array_values(array_filter(
            self::ids($value['entries'] ?? null),
            static fn (int $id): bool => get_post_type($id) === self::POST_TYPE,
        ));

        $semantic = ! array_key_exists('semantic', $value) || (bool) $value['semantic'];

        return ['mode' => $mode, 'topics' => $topics, 'entries' => $entries, 'semantic' => $semantic];
    }

    /**
     * Heading level for the questions. With "semantic" on, it is the first heading of the section body plus one
     * (max. 6). Without a heading in the body, questions are h2. With "semantic" off the result is 0:
     * questions are output without a tag.
     *
     * @param  array<string, mixed>  $selection
     */
    public static function questionLevel(string $body, array $selection): int
    {
        if (! self::sanitize($selection)['semantic']) {
            return 0;
        }

        if (preg_match('/<h([1-6])[\s>]/i', $body, $m) !== 1) {
            return 2;
        }

        return min(6, (int) $m[1] + 1);
    }

    /**
     * Resolves a stored selection to published questions. Deleted or unpublished entries are skipped.
     * Mode "topics" without topics returns every question.
     *
     * @param  array<string, mixed>  $selection
     * @return list<array{question: string, answer: string}>
     */
    public static function entries(array $selection): array
    {
        $clean = self::sanitize($selection);

        if ($clean['mode'] === self::MODE_SELECTED && $clean['entries'] === []) {
            return [];
        }

        $args = [
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'numberposts' => self::LIMIT,
            'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
        ];
        if ($clean['mode'] === self::MODE_SELECTED) {
            $args['post__in'] = $clean['entries'];
        } elseif ($clean['topics'] !== []) {
            $args['tax_query'] = [['taxonomy' => self::TAXONOMY, 'field' => 'term_id', 'terms' => $clean['topics']]];
        }
        $posts = get_posts($args);

        $entries = [];
        foreach ($posts as $post) {
            $entries[] = [
                'question' => $post->post_title,
                'answer' => (string) apply_filters('the_content', $post->post_content),
            ];
        }

        return $entries;
    }

    /**
     * @return list<int>
     */
    private static function ids(mixed $value): array
    {
        $ids = [];
        foreach (is_array($value) ? $value : [] as $raw) {
            $id = is_int($raw) || is_string($raw) ? absint($raw) : 0;
            if ($id > 0 && ! in_array($id, $ids, true)) {
                $ids[] = $id;
            }
            if (count($ids) >= self::LIMIT) {
                break;
            }
        }

        return $ids;
    }
}
