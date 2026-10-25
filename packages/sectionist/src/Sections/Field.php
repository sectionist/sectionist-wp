<?php

declare(strict_types=1);

namespace Sectionist\Sections;

use Sectionist\Faq\Faq;

final readonly class Field
{
    public const TEXT = 'text';

    public const RICHTEXT = 'richtext';

    public const SELECT = 'select';

    public const CHOICE = 'choice';

    public const CTA = 'cta';

    public const IMAGE = 'image';

    public const MEDIA = 'media';

    public const LIST = 'list';

    public const FAQ = 'faq';

    /** Maximum number of entries kept in a list field. */
    public const LIST_LIMIT = 50;

    /** Whitelist for rich-text fields. No inline styles, no font information. */
    public const ALLOWED_HTML = [
        'a' => ['href' => true, 'title' => true],
        'em' => [],
        'h1' => ['class' => true],
        'h2' => ['class' => true],
        'h3' => ['class' => true],
        'h4' => ['class' => true],
        'h5' => ['class' => true],
        'h6' => ['class' => true],
        'li' => [],
        'ol' => [],
        'p' => ['class' => true],
        's' => [],
        'strong' => [],
        'ul' => [],
    ];

    /**
     * Classes allowed on `p`: a paragraph that looks like a heading without being one.
     */
    public const PARAGRAPH_CLASSES = ['as-h1', 'as-h2', 'as-h3', 'as-h4', 'as-h5', 'as-h6'];

    /** Classes allowed on `p` and `h1`-`h6`: text alignment without inline styles. */
    public const ALIGN_CLASSES = ['has-text-align-left', 'has-text-align-center', 'has-text-align-right'];

    /** Link protocols kept by the server. Plain http is intentionally not supported. */
    public const LINK_PROTOCOLS = ['https', 'mailto', 'tel'];

    /**
     * @param  array<array-key, string>  $options  allowed values (select: list, choice: value => label)
     * @param  string|int|array<string, mixed>  $default
     * @param  array{field: string, value: string}|null  $showIf  editor-only visibility condition
     */
    private function __construct(
        public string $kind,
        public string $name,
        public string $label,
        public array $options = [],
        public string|int|array $default = '',
        public ?array $showIf = null,
        public bool $required = false,
        public bool $panel = false,
    ) {}

    /** Show this field in the editor only while another field of the same section has the given value. */
    public function showIf(string $field, string $value): self
    {
        return new self($this->kind, $this->name, $this->label, $this->options, $this->default, ['field' => $field, 'value' => $value], $this->required, $this->panel);
    }

    /** Render this field in the editor as an optional, collapsible panel (switched on and off in its header). */
    public function panel(): self
    {
        return new self($this->kind, $this->name, $this->label, $this->options, $this->default, $this->showIf, $this->required, true);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function optionList(): array
    {
        $list = [];
        foreach ($this->options as $value => $label) {
            $value = $this->kind === self::CHOICE ? (string) $value : $label;
            $list[] = ['value' => $value, 'label' => $label];
        }

        return $list;
    }

    public static function text(string $name, string $label, string $default = ''): self
    {
        return new self(self::TEXT, $name, $label, [], $default);
    }

    public static function richtext(string $name, string $label, string $default = ''): self
    {
        return new self(self::RICHTEXT, $name, $label, [], $default);
    }

    /**
     * @param  list<string>  $options
     */
    public static function select(string $name, string $label, array $options, string $default = ''): self
    {
        return new self(self::SELECT, $name, $label, $options, $default);
    }

    /**
     * Button group for a few options (preferred over a select).
     *
     * @param  array<string, string>  $choices  value => label
     */
    public static function choice(string $name, string $label, array $choices, string $default = ''): self
    {
        return new self(self::CHOICE, $name, $label, $choices, $default);
    }

    /** @param  bool  $required  always active, no switch in the editor (dedicated CTA section) */
    public static function cta(string $name, string $label, bool $required = false): self
    {
        return new self(self::CTA, $name, $label, [], $required ? [...Cta::defaults(), 'active' => true] : Cta::defaults(), null, $required, ! $required);
    }

    /** Media library image, stored as attachment ID (0 = none). */
    public static function image(string $name, string $label): self
    {
        return new self(self::IMAGE, $name, $label, [], 0);
    }

    /** Optional image with position, switchable like the call to action. */
    public static function media(string $name, string $label): self
    {
        return new self(self::MEDIA, $name, $label, [], Media::defaults(), null, false, true);
    }

    /** Dynamic list of entries, each with a `title` and a `text`. */
    public static function list(string $name, string $label): self
    {
        return new self(self::LIST, $name, $label, [], []);
    }

    /** Selection of central FAQ entries: by topics or hand-picked. */
    public static function faq(string $name, string $label): self
    {
        return new self(self::FAQ, $name, $label, [], Faq::defaults());
    }

    /**
     * @return list<array{title: string, text: string}>
     */
    private function sanitizeList(mixed $value): array
    {
        $items = [];
        foreach (is_array($value) ? $value : [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }
            $title = is_string($entry['title'] ?? null) ? sanitize_text_field($entry['title']) : '';
            $text = is_string($entry['text'] ?? null) ? sanitize_textarea_field($entry['text']) : '';
            if ($title === '' && $text === '') {
                continue;
            }
            $items[] = ['title' => $title, 'text' => $text];
            if (count($items) >= self::LIST_LIMIT) {
                break;
            }
        }

        return $items;
    }

    /**
     * @return string|int|array<array-key, mixed>
     */
    public function sanitize(mixed $value): string|int|array
    {
        if ($this->kind === self::LIST) {
            return $this->sanitizeList($value);
        }
        if ($this->kind === self::FAQ) {
            return Faq::sanitize($value);
        }
        if ($this->kind === self::CTA) {
            $cta = Cta::sanitize($value);

            return $this->required ? [...$cta, 'active' => true] : $cta;
        }

        if ($this->kind === self::MEDIA) {
            return Media::sanitize($value);
        }

        if ($this->kind === self::IMAGE) {
            $id = is_int($value) || is_string($value) ? absint($value) : 0;

            return $id > 0 && wp_attachment_is_image($id) ? $id : 0;
        }

        if (! is_string($value)) {
            return $this->default;
        }

        return match ($this->kind) {
            self::TEXT => sanitize_text_field($value),
            self::RICHTEXT => $this->filterClasses(wp_kses($value, self::ALLOWED_HTML, self::LINK_PROTOCOLS)),
            self::CHOICE => array_key_exists($value, $this->options) ? $value : $this->default,
            self::SELECT => in_array($value, $this->options, true) ? $value : $this->default,
            default => $this->default,
        };
    }

    private function filterClasses(string $html): string
    {
        return (string) preg_replace_callback(
            '#<(p|h[1-6]) class="([^"]*)">#i',
            static function (array $m): string {
                $tag = strtolower($m[1]);
                $allowed = $tag === 'p' ? [...self::PARAGRAPH_CLASSES, ...self::ALIGN_CLASSES] : self::ALIGN_CLASSES;
                $classes = array_intersect(preg_split('/\s+/', trim($m[2])) ?: [], $allowed);

                return $classes === [] ? '<'.$tag.'>' : '<'.$tag.' class="'.implode(' ', $classes).'">';
            },
            $html,
        );
    }
}
