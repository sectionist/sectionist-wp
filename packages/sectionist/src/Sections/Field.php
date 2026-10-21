<?php

declare(strict_types=1);

namespace Sectionist\Sections;

final readonly class Field
{
    public const TEXT = 'text';

    public const RICHTEXT = 'richtext';

    public const SELECT = 'select';

    public const CTA = 'cta';

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
     * The theme decides how each class looks.
     */
    public const PARAGRAPH_CLASSES = ['as-h1', 'as-h2', 'as-h3', 'as-h4', 'as-h5', 'as-h6'];

    /** Classes allowed on `p` and `h1`-`h6`: text alignment without inline styles. */
    public const ALIGN_CLASSES = ['has-text-align-left', 'has-text-align-center', 'has-text-align-right'];

    /**
     * @param list<string> $options allowed values (select only)
     * @param string|array<string, mixed> $default
     */
    private function __construct(
        public string $kind,
        public string $name,
        public string $label,
        public array $options = [],
        public string|array $default = '',
    ) {
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

    public static function cta(string $name, string $label): self
    {
        return new self(self::CTA, $name, $label, [], Cta::defaults());
    }

    /**
     * @return string|array<string, mixed>
     */
    public function sanitize(mixed $value): string|array
    {
        if ($this->kind === self::CTA) {
            return Cta::sanitize($value);
        }

        if (! is_string($value)) {
            return $this->default;
        }

        return match ($this->kind) {
            self::TEXT => sanitize_text_field($value),
            self::RICHTEXT => $this->filterClasses(wp_kses($value, self::ALLOWED_HTML)),
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

                return $classes === [] ? '<' . $tag . '>' : '<' . $tag . ' class="' . implode(' ', $classes) . '">';
            },
            $html,
        );
    }
}
