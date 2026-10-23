<?php

declare(strict_types=1);

namespace Sectionist\Sections;

/**
 * Section backgrounds offered to editors. The active theme declares them in
 * `sectionist.json` (key `backgrounds`); the filter `sectionist/backgrounds` can adjust
 * the list. Without any, no choice is shown. Only the key is stored. Colors are not
 * known to the plugin: the theme styles `.section--bg-<key>` in its own CSS.
 */
final class Backgrounds
{
    public const FILTER = 'sectionist/backgrounds';

    public const CONFIG_FILE = 'sectionist.json';

    public const STYLES_FILTER = 'sectionist/editor_styles';

    /** @var array<string, array<string, mixed>> */
    private static array $configs = [];

    /**
     * @return list<array{key: string, label: string}>
     */
    public static function all(): array
    {
        $items = apply_filters(self::FILTER, self::fromConfig());
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
            ];
        }

        return $backgrounds;
    }

    /**
     * Stylesheets the editor loads so it can show the `.section--bg-<key>` colors of the
     * frontend. The config key `editor_styles` lists theme-relative paths or absolute
     * URLs; the filter `sectionist/editor_styles` can add or replace entries (e.g. hashed
     * build output from a Vite manifest). Local files are versioned by modification time.
     *
     * @return list<array{url: string, version: string|null}>
     */
    public static function editorStyles(): array
    {
        $entries = self::config()['editor_styles'] ?? [];
        $entries = apply_filters(self::STYLES_FILTER, is_array($entries) ? array_values($entries) : []);
        if (! is_array($entries)) {
            return [];
        }

        $styles = [];
        foreach ($entries as $entry) {
            if (! is_string($entry) || $entry === '') {
                continue;
            }
            if (preg_match('#^(https?:)?//#', $entry) === 1) {
                $styles[] = ['url' => $entry, 'version' => null];

                continue;
            }
            if (str_contains($entry, '..')) {
                continue;
            }

            $path = get_stylesheet_directory().'/'.ltrim($entry, '/');
            if (is_file($path)) {
                $styles[] = [
                    'url' => get_stylesheet_directory_uri().'/'.ltrim($entry, '/'),
                    'version' => (string) filemtime($path),
                ];
            }
        }

        return $styles;
    }

    /**
     * @return list<mixed>
     */
    private static function fromConfig(): array
    {
        $items = self::config()['backgrounds'] ?? null;

        return is_array($items) ? array_values($items) : [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function config(): array
    {
        $file = get_stylesheet_directory().'/'.self::CONFIG_FILE;
        if (isset(self::$configs[$file])) {
            return self::$configs[$file];
        }

        $json = is_readable($file) ? file_get_contents($file) : false;
        $config = $json === false ? null : json_decode($json, true);

        return self::$configs[$file] = is_array($config) ? $config : [];
    }
}
