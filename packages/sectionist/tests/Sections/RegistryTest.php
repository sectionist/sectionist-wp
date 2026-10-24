<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Cta;
use Sectionist\Sections\Media;
use Sectionist\Sections\Registry;
use Sectionist\Sections\Types\TextType;
use Sectionist\Storage\Section;
use Sectionist\Tests\Support\WpState;

#[CoversClass(Registry::class)]
final class RegistryTest extends TestCase
{
    protected function setUp(): void
    {
        WpState::reset();
    }

    public function test_types_register_via_hook(): void
    {
        add_action(Registry::HOOK, static fn (Registry $r) => $r->register(new TextType));

        $registry = new Registry;

        $this->assertInstanceOf(TextType::class, $registry->get('text'));
    }

    public function test_unknown_name_returns_null(): void
    {
        $this->assertNull((new Registry)->get('missing'));
    }

    public function test_duplicate_is_rejected(): void
    {
        $registry = new Registry;
        $registry->register(new TextType);

        $this->expectException(InvalidArgumentException::class);
        $registry->register(new TextType);
    }

    public function test_sanitize_sections_preserves_unknown_types_and_drops_invalid_entries(): void
    {
        $registry = new Registry;
        $registry->register(new TextType);
        $unknown = ['id' => 'u1', 'type' => 'hero', 'data' => ['x' => '<i>raw</i>']];

        $clean = $registry->sanitizeSections([
            ['id' => 'a', 'type' => 'text', 'data' => ['body' => '<i>H</i>']],
            $unknown,
            ['type' => 'text'],
            'garbage',
        ]);

        $this->assertEquals(
            [new Section('a', 'text', data: ['body' => 'H', 'media' => 'none', 'image' => Media::defaults(), 'cta' => Cta::defaults()]), new Section('u1', 'hero', data: ['x' => '<i>raw</i>'])],
            $clean,
        );
    }

    public function test_container_fields_active_and_background_are_sanitized(): void
    {
        $registry = new Registry;
        $registry->register(new TextType);

        $clean = $registry->sanitizeSections([
            ['id' => 'a', 'type' => 'text', 'active' => false, 'background' => 'Dark<b>', 'data' => []],
            ['id' => 'b', 'type' => 'text', 'data' => []],
        ]);

        $this->assertFalse($clean[0]->active);
        $this->assertSame('darkb', $clean[0]->background);
        $this->assertTrue($clean[1]->active);
        $this->assertSame('', $clean[1]->background);
    }
}
