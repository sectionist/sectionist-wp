<?php

declare(strict_types=1);

namespace Sectionist\Tests\Storage;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Storage\Section;

#[CoversClass(Section::class)]
final class SectionTest extends TestCase
{
    public function test_missing_or_invalid_fields_fall_back_to_defaults(): void
    {
        $section = Section::fromArray(['id' => 5, 'data' => 'x', 'background' => []]);

        $this->assertSame('', $section->id);
        $this->assertSame('', $section->type);
        $this->assertTrue($section->active);
        $this->assertSame('', $section->background);
        $this->assertSame([], $section->data);
    }

    public function test_unknown_container_fields_survive_round_trip(): void
    {
        $raw = ['future' => 1, 'id' => 'a', 'type' => 'text', 'active' => false, 'background' => 'dark', 'data' => ['x' => 1]];

        $this->assertEquals($raw, Section::fromArray($raw)->toArray());
    }
}
