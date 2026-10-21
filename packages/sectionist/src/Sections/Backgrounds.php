<?php

declare(strict_types=1);

namespace Sectionist\Sections;

/**
 * Section backgrounds offered to editors. The active theme (or a plugin) provides
 * them through the filter `sectionist/backgrounds`; without any, no choice is shown.
 * Only the key is stored, the theme decides how it looks.
 */
final class Backgrounds
{
    public const FILTER = 'sectionist/backgrounds';

    /**
     * @return list<array{key: string, label: string, color: string}>
     */
    public static function all(): array
    {
        $items = apply_filters(self::FILTER, []);
        if (! is_array($items)) {
            return [];
        }

        $backgrounds = [];
        foreach ($items as $item) {
            if (! is_array($item) || ! is_string($item['key'] ?? null) || $item['key'] === '') {
                continue;
            }

            $backgrounds[] = [
                'key' => sanitize_key($item['key']),
                'label' => is_string($item['label'] ?? null) ? $item['label'] : $item['key'],
                'color' => is_string($item['color'] ?? null) ? $item['color'] : '',
            ];
        }

        return $backgrounds;
    }
}
