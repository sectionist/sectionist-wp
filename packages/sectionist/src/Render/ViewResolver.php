<?php

declare(strict_types=1);

namespace Sectionist\Render;

final class ViewResolver
{
    public const THEME_DIRECTORY = 'sectionist';

    /**
     * @param  list<string>  $roots  Directories searched in order, first match wins.
     */
    public function __construct(private readonly array $roots) {}

    /**
     * Resolution order: active theme, plugin fallback.
     */
    public static function forWordPress(string $pluginDir): self
    {
        $roots = [
            get_stylesheet_directory().'/'.self::THEME_DIRECTORY,
            rtrim($pluginDir, '/').'/views',
        ];

        return new self(array_values(array_unique($roots)));
    }

    public function resolve(string $type): ?string
    {
        if (preg_match('/^[a-z0-9_-]+$/', $type) !== 1) {
            return null;
        }

        foreach ($this->roots as $root) {
            $file = rtrim($root, '/').'/'.$type.'.php';
            if (is_file($file)) {
                return $file;
            }
        }

        return null;
    }
}
