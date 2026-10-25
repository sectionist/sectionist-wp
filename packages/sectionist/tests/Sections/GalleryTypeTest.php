<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Field;
use Sectionist\Sections\Types\GalleryType;

#[CoversClass(GalleryType::class)]
#[CoversClass(Field::class)]
final class GalleryTypeTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['sectionist_test_images'] = [5, 6];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['sectionist_test_images']);
    }

    public function test_defaults_have_empty_images(): void
    {
        $this->assertSame([], (new GalleryType)->defaults()['images']);
    }

    public function test_keeps_valid_images_and_drops_invalid_ones(): void
    {
        $clean = (new GalleryType)->sanitize(['images' => [
            ['id' => 5, 'caption' => 'A'],
            ['id' => 99, 'caption' => 'not an image'],
            'nope',
            ['id' => '6'],
        ]]);

        $this->assertSame([['id' => 5, 'caption' => 'A'], ['id' => 6, 'caption' => '']], $clean['images']);
    }

    public function test_invalid_value_becomes_empty_list(): void
    {
        $this->assertSame([], (new GalleryType)->sanitize(['images' => 'x'])['images']);
    }

    public function test_images_are_capped(): void
    {
        $GLOBALS['sectionist_test_images'] = [5];
        $many = array_fill(0, Field::LIST_LIMIT + 10, ['id' => 5, 'caption' => '']);

        $this->assertCount(Field::LIST_LIMIT, (new GalleryType)->sanitize(['images' => $many])['images']);
    }
}
