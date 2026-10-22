<?php

declare(strict_types=1);

namespace Sectionist\Sections;

/**
 * Reusable call-to-action value: label, style and a target (existing page or custom URL, optionally in a new tab).
 * Any section type can use it through `Field::cta()`. Page links are resolved at render
 * time, so permalink changes never break stored data.
 */
final class Cta
{
    public const STYLES = ['button', 'link'];

    /**
     * @return array{active: bool, label: string, style: string, page_id: int, url: string, new_tab: bool}
     */
    public static function defaults(): array
    {
        return [
            'active' => false,
            'label' => '',
            'style' => 'button',
            'page_id' => 0,
            'url' => '',
            'new_tab' => false,
        ];
    }

    /**
     * @return array{active: bool, label: string, style: string, page_id: int, url: string, new_tab: bool}
     */
    public static function sanitize(mixed $value): array
    {
        $in = is_array($value) ? $value : [];
        $defaults = self::defaults();
        $str = static fn (string $key): string => is_string($in[$key] ?? null) ? sanitize_text_field($in[$key]) : '';
        $style = $str('style');

        return [
            'active' => ($in['active'] ?? false) === true,
            'label' => $str('label'),
            'style' => in_array($style, self::STYLES, true) ? $style : $defaults['style'],
            'page_id' => is_int($in['page_id'] ?? null) || is_string($in['page_id'] ?? null) ? absint($in['page_id']) : 0,
            'url' => is_string($in['url'] ?? null) ? esc_url_raw($in['url']) : '',
            'new_tab' => ($in['new_tab'] ?? false) === true,
        ];
    }

    /**
     * Target URL of a sanitized CTA: a custom URL wins over the selected page, or '' when there is none (e.g. page missing or unpublished).
     *
     * @param  array<string, mixed>  $cta
     */
    public static function url(array $cta): string
    {
        if (is_string($cta['url'] ?? null) && $cta['url'] !== '') {
            return $cta['url'];
        }

        $pageId = is_int($cta['page_id'] ?? null) ? $cta['page_id'] : 0;
        if ($pageId <= 0 || get_post_status($pageId) !== 'publish') {
            return '';
        }
        $permalink = get_permalink($pageId);

        return is_string($permalink) ? $permalink : '';
    }
}
