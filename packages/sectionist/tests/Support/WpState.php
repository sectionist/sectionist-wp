<?php

declare(strict_types=1);

namespace Sectionist\Tests\Support;

final class WpState
{
    /** @var array<string, list<callable>> */
    public static array $actions = [];

    /** @var array<string, mixed> */
    public static array $options = [];

    /** @var array<string, mixed> */
    public static array $transients = [];

    /** @var array<int, string> */
    public static array $content = [];

    public static bool $canEdit = true;

    public static string $themeDir = '';

    public static ?\WP_Screen $screen = null;

    public static bool $editorSupport = false;

    public static function reset(): void
    {
        self::$actions = [];
        self::$options = [];
        self::$transients = [];
        self::$content = [];
        self::$canEdit = true;
        self::$themeDir = '';
        self::$screen = null;
        self::$editorSupport = false;
    }
}
