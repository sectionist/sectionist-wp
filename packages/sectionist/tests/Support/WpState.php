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

    /** @var array<string, mixed> */
    public static array $meta = [];

    public static bool $canEdit = true;

    public static string $childDir = '';

    public static string $parentDir = '';

    public static function reset(): void
    {
        self::$actions = [];
        self::$options = [];
        self::$transients = [];
        self::$meta = [];
        self::$canEdit = true;
        self::$childDir = '';
        self::$parentDir = '';
    }
}
