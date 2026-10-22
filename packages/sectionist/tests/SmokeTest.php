<?php

declare(strict_types=1);

namespace Sectionist\Tests;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Sectionist\Plugin;

#[CoversNothing]
final class SmokeTest extends TestCase
{
    public function test_plugin_class_is_autoloadable(): void
    {
        $this->assertTrue(class_exists(Plugin::class));
    }
}
