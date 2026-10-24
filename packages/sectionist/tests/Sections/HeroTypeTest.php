<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Field;
use Sectionist\Sections\Types\HeroType;

#[CoversClass(HeroType::class)]
#[CoversClass(Field::class)]
final class HeroTypeTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['sectionist_test_images'] = [7];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['sectionist_test_images']);
    }

    public function test_keeps_valid_image_id(): void
    {
        $this->assertSame(7, (new HeroType)->sanitize(['image' => '7'])['image']);
    }

    public function test_media_mode_defaults_to_none_and_rejects_unknown(): void
    {
        $this->assertSame('none', (new HeroType)->defaults()['media']);
        $this->assertSame('none', (new HeroType)->sanitize(['media' => 'video'])['media']);
        $this->assertSame('background', (new HeroType)->sanitize(['media' => 'background'])['media']);
        $this->assertSame('image', (new HeroType)->sanitize(['media' => 'image'])['media']);
    }

    public function test_image_field_is_conditional_on_media_mode(): void
    {
        $fields = array_column((new HeroType)->fields(), null, 'name');

        $this->assertSame(['field' => 'media', 'value' => 'image'], $fields['image']->showIf);
        $this->assertNull($fields['media']->showIf);
    }

    public function test_drops_unknown_or_invalid_image(): void
    {
        $this->assertSame(0, (new HeroType)->sanitize(['image' => 8])['image']);
        $this->assertSame(0, (new HeroType)->sanitize(['image' => ['x']])['image']);
        $this->assertSame(0, (new HeroType)->sanitize([])['image']);
    }

    public function test_defaults_have_no_image(): void
    {
        $this->assertSame(0, (new HeroType)->defaults()['image']);
    }
}
