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

    public static ?int $currentPostId = null;

    public static bool $canEdit = true;

    public static string $themeDir = '';

    public static ?\WP_Screen $screen = null;

    public static bool $editorSupport = false;

    /** @var array<int, string> post ID => post type */
    public static array $postTypes = [];

    /** @var list<int> existing topic term IDs */
    public static array $terms = [];

    public static function reset(): void
    {
        self::$actions = [];
        self::$options = [];
        self::$transients = [];
        self::$content = [];
        self::$currentPostId = null;
        self::$canEdit = true;
        self::$themeDir = '';
        self::$screen = null;
        self::$editorSupport = false;
        self::$postTypes = [];
        self::$terms = [];
    }
}
