<?php

declare(strict_types=1);

namespace Sectionist\Admin;

use Sectionist\Sections\Registry;

/**
 * Settings -> Sectionist: which section types may be added per post type.
 * Stored as the denied cells only, so newly registered types are allowed by default.
 * The filter `sectionist/sections/allowed` has the last word.
 */
final readonly class Settings
{
    public const OPTION = 'sectionist_settings';

    public const GROUP = 'sectionist_settings_group';

    public const PAGE = 'sectionist';

    public const FILTER = 'sectionist/sections/allowed';

    public function __construct(private Registry $registry) {}

    /**
     * Effective result: stored setting, then the filter.
     */
    public static function isAllowed(string $postType, string $type): bool
    {
        return (bool) apply_filters(self::FILTER, self::isStoredAllowed($postType, $type), $type, $postType);
    }

    public static function isStoredAllowed(string $postType, string $type): bool
    {
        return ! in_array($type, self::disabled()[$postType] ?? [], true);
    }

    /**
     * @return array<string, list<string>>
     */
    private static function disabled(): array
    {
        $option = get_option(self::OPTION, []);
        $raw = is_array($option) && is_array($option['disabled'] ?? null) ? $option['disabled'] : [];

        $disabled = [];
        foreach ($raw as $postType => $types) {
            if (is_string($postType) && is_array($types)) {
                $disabled[$postType] = array_values(array_filter($types, 'is_string'));
            }
        }

        return $disabled;
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addPage']);
        add_action('admin_init', [$this, 'registerSetting']);
        add_filter('plugin_action_links_'.plugin_basename(dirname(__DIR__, 2).'/sectionist.php'), [$this, 'actionLinks']);
    }

    /**
     * @param  array<int|string, string>  $links
     * @return array<int|string, string>
     */
    public function actionLinks(array $links): array
    {
        $url = admin_url('options-general.php?page='.self::PAGE);
        array_unshift($links, '<a href="'.esc_url($url).'">'.esc_html__('Settings', 'sectionist').'</a>');

        return $links;
    }

    public function addPage(): void
    {
        add_options_page(
            __('Sectionist', 'sectionist'),
            __('Sectionist', 'sectionist'),
            'manage_options',
            self::PAGE,
            [$this, 'render'],
        );
    }

    public function registerSetting(): void
    {
        register_setting(self::GROUP, self::OPTION, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize'],
            'default' => [],
        ]);
    }

    /**
     * Unchecked, unlocked cells become denied. Locked cells (decided by the filter) keep
     * their stored value, as do cells of post types or types that are not listed right now.
     *
     * @return array{disabled: array<string, list<string>>}
     */
    public function sanitize(mixed $input): array
    {
        $matrix = is_array($input) && is_array($input['allowed'] ?? null) ? $input['allowed'] : [];
        $disabled = self::disabled();

        foreach (MetaBox::postTypes() as $postType) {
            $posted = is_array($matrix[$postType] ?? null) ? $matrix[$postType] : [];
            foreach (array_keys($this->registry->all()) as $type) {
                if ($this->isLocked($postType, $type)) {
                    continue;
                }

                $current = array_diff($disabled[$postType] ?? [], [$type]);
                if (! isset($posted[$type])) {
                    $current[] = $type;
                }
                $disabled[$postType] = array_values($current);
            }
        }

        return ['disabled' => array_filter($disabled, static fn (array $types): bool => $types !== [])];
    }

    private function isLocked(string $postType, string $type): bool
    {
        return self::isStoredAllowed($postType, $type) !== self::isAllowed($postType, $type);
    }

    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $postTypes = MetaBox::postTypes();
        $types = $this->registry->all();

        echo '<div class="wrap"><h1>'.esc_html__('Sectionist', 'sectionist').'</h1>';
        echo '<p>'.esc_html__('Choose which section types editors can add for each content type. Existing sections of a disabled type are kept and still rendered.', 'sectionist').'</p>';
        echo '<form method="post" action="options.php">';
        settings_fields(self::GROUP);
        echo '<style>.sectionist-settings__table th input[type=checkbox]{margin:0 4px 0 0}.sectionist-settings__table td input[type=checkbox]{margin:0}</style>';
        echo '<table class="widefat striped sectionist-settings__table" style="max-width:720px"><thead><tr><th>'.esc_html__('Section type', 'sectionist').'</th>';
        foreach ($postTypes as $postType) {
            $object = get_post_type_object($postType);
            $labels = is_object($object) && isset($object->labels) && is_object($object->labels) ? $object->labels : null;
            $label = $labels !== null && isset($labels->name) && is_string($labels->name) ? $labels->name : $postType;
            printf(
                '<th><label><input type="checkbox" data-all-col="%s"> %s</label></th>',
                esc_attr($postType),
                esc_html($label),
            );
        }
        echo '</tr></thead><tbody>';

        foreach ($types as $name => $type) {
            echo '<tr><td>'.esc_html($type->label()).'</td>';
            foreach ($postTypes as $postType) {
                $locked = $this->isLocked($postType, $name);
                printf(
                    '<td><input type="checkbox" name="%s[allowed][%s][%s]" value="1" data-col="%s" aria-label="%s"%s%s></td>',
                    esc_attr(self::OPTION),
                    esc_attr($postType),
                    esc_attr($name),
                    esc_attr($postType),
                    esc_attr($type->label().' – '.$postType),
                    self::isAllowed($postType, $name) ? ' checked' : '',
                    $locked ? ' disabled' : '',
                );
            }
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '<p class="description">'.esc_html__('Disabled checkboxes are set in code and cannot be changed here.', 'sectionist').'</p>';
        submit_button();
        echo '</form></div>';
        echo <<<'HTML'
<script>
(function () {
	var root = document.querySelector('.sectionist-settings__table');
	if (!root) { return; }
	var cells = function (attr, value) {
		return [].slice.call(root.querySelectorAll('input[' + attr + '="' + value + '"]:not([disabled])'));
	};
	var sync = function () {
		[].forEach.call(root.querySelectorAll('[data-all-col]'), function (box) { state(box, cells('data-col', box.getAttribute('data-all-col'))); });
	};
	var state = function (box, list) {
		var on = list.filter(function (c) { return c.checked; }).length;
		box.disabled = list.length === 0;
		box.checked = list.length > 0 && on === list.length;
		box.indeterminate = on > 0 && on < list.length;
	};
	root.addEventListener('change', function (e) {
		var t = e.target;
		if (t.hasAttribute('data-all-col')) { cells('data-col', t.getAttribute('data-all-col')).forEach(function (c) { c.checked = t.checked; }); }
		sync();
	});
	sync();
})();
</script>
HTML;
    }
}
