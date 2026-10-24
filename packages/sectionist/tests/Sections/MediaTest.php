<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Media;

final class MediaTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['sectionist_test_images'] = [7];
    }

    public function test_invalid_input_gives_inactive_defaults(): void
    {
        $this->assertSame(Media::defaults(), Media::sanitize('nope'));
    }

    public function test_fields_are_sanitized(): void
    {
        $media = Media::sanitize(['active' => true, 'id' => '7', 'position' => 'left']);

        $this->assertSame(['active' => true, 'id' => 7, 'position' => 'left'], $media);
    }

    public function test_unknown_position_and_non_image_are_rejected(): void
    {
        $media = Media::sanitize(['active' => true, 'id' => 99, 'position' => 'diagonal']);

        $this->assertSame(0, $media['id']);
        $this->assertSame('right', $media['position']);
    }

    public function test_id_is_zero_unless_active_with_image(): void
    {
        $this->assertSame(7, Media::id(['active' => true, 'id' => 7]));
        $this->assertSame(0, Media::id(['active' => false, 'id' => 7]));
        $this->assertSame(0, Media::id(['active' => true, 'id' => 0]));
        $this->assertSame(0, Media::id([]));
    }
}
