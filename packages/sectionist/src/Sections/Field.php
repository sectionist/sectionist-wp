<?php

declare(strict_types=1);

namespace Sectionist\Sections;

final readonly class Field
{
    public const TEXT = 'text';

    public const TEXTAREA = 'textarea';

    public const SELECT = 'select';

    /** Whitelist for rich-text fields. No inline styles, no font information. */
    public const ALLOWED_HTML = [
        'a' => ['href' => true, 'title' => true],
        'em' => [],
        'h1' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'h5' => [],
        'h6' => [],
        'li' => [],
        'ol' => [],
        'p' => ['class' => true],
        'strong' => [],
        'ul' => [],
    ];

    /**
     * Classes allowed on `p`: a paragraph that looks like a heading without being one.
     * The theme decides how each class looks.
     */
    public const PARAGRAPH_CLASSES = ['as-h1', 'as-h2', 'as-h3', 'as-h4', 'as-h5', 'as-h6'];

    /**
     * @param list<string> $options allowed values (select only)
     */
    private function __construct(
        public string $kind,
        public string $name,
        public string $label,
        public array $options = [],
        public string $default = '',
    ) {
    }

    public static function text(string $name, string $label, string $default = ''): self
    {
        return new self(self::TEXT, $name, $label, [], $default);
    }

    public static function textarea(string $name, string $label, string $default = ''): self
    {
        return new self(self::TEXTAREA, $name, $label, [], $default);
    }

    /**
     * @param  list<string>  $options
     */
    public static function select(string $name, string $label, array $options, string $default = ''): self
    {
        return new self(self::SELECT, $name, $label, $options, $default);
    }

    public function sanitize(mixed $value): string
    {
        if (! is_string($value)) {
            return $this->default;
        }

        return match ($this->kind) {
            self::TEXT => sanitize_text_field($value),
            self::TEXTAREA => $this->filterParagraphClasses(wp_kses($value, self::ALLOWED_HTML)),
            self::SELECT => in_array($value, $this->options, true) ? $value : $this->default,
            default => $this->default,
        };
    }

    private function filterParagraphClasses(string $html): string
    {
        return (string) preg_replace_callback(
            '#<p class="([^"]*)">#i',
            static function (array $m): string {
                $classes = array_intersect(preg_split('/\s+/', trim($m[1])) ?: [], self::PARAGRAPH_CLASSES);

                return $classes === [] ? '<p>' : '<p class="' . implode(' ', $classes) . '">';
            },
            $html,
        );
    }
}
