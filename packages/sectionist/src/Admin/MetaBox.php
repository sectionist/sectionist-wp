<?php

declare(strict_types=1);

namespace Sectionist\Admin;

use Sectionist\Rest\PreviewController;
use Sectionist\Sections\Registry;
use Sectionist\Storage\Repository;

final class MetaBox
{
    public function __construct(
        private readonly Registry $registry,
        private readonly Repository $repository,
    ) {
    }

    /**
     * Post types that get the editor. Filter `sectionist/post_types` makes this configurable.
     *
     * @return list<string>
     */
    public static function postTypes(): array
    {
        $types = apply_filters('sectionist/post_types', ['page']);

        return is_array($types) ? array_values(array_filter($types, 'is_string')) : ['page'];
    }

    public function register(): void
    {
        add_action('add_meta_boxes', [$this, 'add']);
        add_action('init', [$this, 'disableDefaultEditor'], 100);
        add_filter('use_block_editor_for_post_type', [$this, 'useBlockEditor'], 100, 2);
    }

    /**
     * Sectionist replaces the content editor, so the default one is removed.
     */
    public function disableDefaultEditor(): void
    {
        foreach (self::postTypes() as $postType) {
            remove_post_type_support($postType, 'editor');
        }
    }

    public function useBlockEditor(bool $use, string $postType): bool
    {
        return in_array($postType, self::postTypes(), true) ? false : $use;
    }

    public function add(): void
    {
        foreach (self::postTypes() as $postType) {
            add_meta_box('sectionist', __('Sections', 'sectionist'), [$this, 'render'], $postType, 'normal', 'high');
        }
    }

    public function render(mixed $post): void
    {
        $postId = is_object($post) && isset($post->ID) ? (int) $post->ID : 0;
        $document = $this->repository->load($postId);

        if ($document->isNewerThanSupported()) {
            echo '<p>' . esc_html__('These sections were saved by a newer version of Sectionist. Please update the plugin to edit them.', 'sectionist') . '</p>';

            return;
        }

        $config = wp_json_encode([
            'preview' => [
                'url' => rest_url(PreviewController::NAMESPACE . PreviewController::ROUTE),
                'nonce' => wp_create_nonce('wp_rest'),
                'postId' => $postId,
            ],
            'types' => $this->typeDefinitions(),
            'sections' => $document->sections,
            'i18n' => $this->labels(),
        ]);

        wp_nonce_field(SaveHandler::NONCE_ACTION, SaveHandler::NONCE_FIELD);
        printf(
            '<div id="sectionist-editor" data-config="%s"></div><input type="hidden" id="sectionist-data" name="%s" value="">',
            esc_attr($config === false ? '{}' : $config),
            esc_attr(SaveHandler::DATA_FIELD),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function typeDefinitions(): array
    {
        $types = [];
        foreach ($this->registry->all() as $type) {
            $types[] = [
                'name' => $type->name(),
                'label' => $type->label(),
                'description' => $type->description(),
                'icon' => $type->icon(),
                'defaults' => $type->defaults(),
                'fields' => array_map(
                    static fn ($field): array => [
                        'kind' => $field->kind,
                        'name' => $field->name,
                        'label' => $field->label,
                        'options' => $field->options,
                    ],
                    $type->fields(),
                ),
            ];
        }

        return $types;
    }

    /**
     * @return array<string, string>
     */
    private function labels(): array
    {
        return [
            'tabSections' => __('Section Editor', 'sectionist'),
            'tabPreview' => __('Preview', 'sectionist'),
            'previewLoading' => __('Loading preview…', 'sectionist'),
            'previewError' => __('The preview could not be loaded.', 'sectionist'),
            'previewEmpty' => __('Nothing to preview yet.', 'sectionist'),
            'tabSeo' => __('SEO', 'sectionist'),
            'seoSoon' => __('SEO settings are coming soon.', 'sectionist'),
            'rtBlock' => __('Text format', 'sectionist'),
            'rtParagraph' => __('Paragraph', 'sectionist'),
            'rtHeading' => __('Heading', 'sectionist'),
            'rtLook' => __('looks like', 'sectionist'),
            'rtLookNone' => __('Normal', 'sectionist'),
            'rtBold' => __('Bold', 'sectionist'),
            'rtItalic' => __('Italic', 'sectionist'),
            'rtStrike' => __('Strikethrough', 'sectionist'),
            'rtBulletList' => __('Bulleted list', 'sectionist'),
            'rtOrderedList' => __('Numbered list', 'sectionist'),
            'rtAlignLeft' => __('Align left', 'sectionist'),
            'rtAlignCenter' => __('Align center', 'sectionist'),
            'rtAlignRight' => __('Align right', 'sectionist'),
            'rtLink' => __('Link', 'sectionist'),
            'rtLinkPrompt' => __('Link URL (empty removes the link)', 'sectionist'),
            'closeAll' => __('Collapse all sections', 'sectionist'),
            'expandAll' => __('Expand all sections', 'sectionist'),
            'chooseType' => __('Choose a section type', 'sectionist'),
            'cancel' => __('Cancel', 'sectionist'),
            'drag' => __('Drag to reorder', 'sectionist'),
            'toggle' => __('Expand or collapse section', 'sectionist'),
            'add' => __('Add section', 'sectionist'),
            'remove' => __('Remove', 'sectionist'),
            'confirmRemoveTitle' => __('Delete section?', 'sectionist'),
            'confirmRemoveText' => __('This section and its content will be removed once you save the page.', 'sectionist'),
            'confirmRemoveAction' => __('Delete section', 'sectionist'),
            'moveUp' => __('Move up', 'sectionist'),
            'moveDown' => __('Move down', 'sectionist'),
            'empty' => __('No sections yet. Add the first one below.', 'sectionist'),
            'unknownType' => __('This section type is not available. Its content is kept unchanged.', 'sectionist'),
            'saveError' => __('The sections could not be prepared for saving. Please reload the page.', 'sectionist'),
        ];
    }
}
