<?php

declare(strict_types=1);

namespace Sectionist\Admin;

use Sectionist\Rest\PreviewController;
use Sectionist\Sections\Backgrounds;
use Sectionist\Sections\Field;
use Sectionist\Sections\Registry;
use Sectionist\Storage\Repository;

final readonly class MetaBox
{
    public function __construct(
        private Registry $registry,
        private Repository $repository,
    ) {}

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
        add_action('admin_notices', [$this, 'warnAboutOtherEditor']);
    }

    /**
     * Another plugin re-enabled the content editor for one of our post types.
     * Two editors on one `post_content` field cannot work.
     */
    public function warnAboutOtherEditor(): void
    {
        $screen = get_current_screen();
        $postType = $screen->post_type ?? '';
        if ($screen === null || $screen->base !== 'post' || ! in_array($postType, self::postTypes(), true)) {
            return;
        }

        if (! post_type_supports($postType, 'editor')) {
            return;
        }

        echo '<div class="notice notice-warning"><p>'
            .esc_html__('Sectionist: another plugin enabled the default editor for this content type. Editing with it can overwrite the sections. Only the sections are saved here, other content is ignored.', 'sectionist')
            .'</p></div>';
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
            echo '<p>'.esc_html__('These sections were saved by a newer version of Sectionist. Please update the plugin to edit them.', 'sectionist').'</p>';

            return;
        }

        $config = wp_json_encode([
            'preview' => [
                'url' => rest_url(PreviewController::NAMESPACE.PreviewController::ROUTE),
                'nonce' => wp_create_nonce('wp_rest'),
                'postId' => $postId,
                'pagesUrl' => rest_url('wp/v2/pages'),
            ],
            'types' => $this->typeDefinitions(),
            'backgrounds' => Backgrounds::all(),
            'sections' => $document->toArray()['sections'],
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
                    static fn (Field $field): array => [
                        'kind' => $field->kind,
                        'name' => $field->name,
                        'label' => $field->label,
                        'options' => $field->optionList(),
                        'showIf' => $field->showIf,
                        'required' => $field->required,
                        'panel' => $field->panel,
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
            'rtLinkTitle' => __('Link', 'sectionist'),
            'rtLinkType' => __('Link type', 'sectionist'),
            'rtLinkAddress' => __('Address', 'sectionist'),
            'rtLinkText' => __('Link text', 'sectionist'),
            'rtLinkApply' => __('Apply', 'sectionist'),
            'rtLinkRemove' => __('Remove link', 'sectionist'),
            'imageChoose' => __('Choose image', 'sectionist'),
            'imageReplace' => __('Replace image', 'sectionist'),
            'imageRemove' => __('Remove image', 'sectionist'),
            'mediaPosition' => __('Image position', 'sectionist'),
            'mediaLeft' => __('Left', 'sectionist'),
            'mediaRight' => __('Right', 'sectionist'),
            'mediaTop' => __('Top', 'sectionist'),
            'mediaBottom' => __('Bottom', 'sectionist'),
            'ctaLabel' => __('Button / link label', 'sectionist'),
            'ctaStyle' => __('Display as', 'sectionist'),
            'ctaButton' => __('Button', 'sectionist'),
            'ctaLink' => __('Link', 'sectionist'),
            'ctaPage' => __('Select a page', 'sectionist'),
            'ctaPageNone' => __('— No page —', 'sectionist'),
            'ctaUrl' => __('Or custom URL', 'sectionist'),
            'ctaNewTab' => __('Open link in a new tab', 'sectionist'),
            'active' => __('Active', 'sectionist'),
            'inactive' => __('Inactive', 'sectionist'),
            'toggleActive' => __('Show or hide this section on the page', 'sectionist'),
            'background' => __('Background color', 'sectionist'),
            'backgroundNone' => __('None', 'sectionist'),
            'showJson' => __('Show JSON', 'sectionist'),
            'jsonTitle' => __('Current JSON (unsaved state)', 'sectionist'),
            'jsonCopy' => __('Copy', 'sectionist'),
            'jsonCopied' => __('Copied', 'sectionist'),
            'close' => __('Close', 'sectionist'),
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
            'listTitle' => __('Title', 'sectionist'),
            'listText' => __('Text', 'sectionist'),
            'listAdd' => __('Add entry', 'sectionist'),
            'listRemove' => __('Remove entry', 'sectionist'),
            'listEntry' => __('Entry', 'sectionist'),
            'empty' => __('No sections yet. Add the first one below.', 'sectionist'),
            'unavailable' => __('Unavailable', 'sectionist'),
            'unknownType' => __('This section type is not available. Its content is kept unchanged.', 'sectionist'),
            'saveError' => __('The sections could not be prepared for saving. Please reload the page.', 'sectionist'),
            'backdropTitle' => __('Backdrop not set', 'sectionist'),
            'backdropText' => __('Some hero sections have “Color” or “Image” selected, but no color or image chosen. Their backdrop will be set to “None”.', 'sectionist'),
            'backdropConfirm' => __('Set to None and save', 'sectionist'),
            'unsavedTitle' => __('Leave without saving?', 'sectionist'),
            'unsavedText' => __('You have unsaved changes to the sections. If you leave now, they will be lost.', 'sectionist'),
            'unsavedLeave' => __('Discard changes', 'sectionist'),
            'unsavedStay' => __('Stay on page', 'sectionist'),
        ];
    }
}
