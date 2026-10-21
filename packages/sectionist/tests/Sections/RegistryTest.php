<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Registry;
use Sectionist\Sections\Types\TextType;
use Sectionist\Tests\Support\WpState;

#[CoversClass(Registry::class)]
final class RegistryTest extends TestCase
{
    protected function setUp(): void
    {
        WpState::reset();
    }

    public function testTypesRegisterViaHook(): void
    {
        add_action(Registry::HOOK, static fn (Registry $r) => $r->register(new TextType()));

        $registry = new Registry();

        $this->assertInstanceOf(TextType::class, $registry->get('text'));
    }

    public function testUnknownNameReturnsNull(): void
    {
        $this->assertNull((new Registry())->get('missing'));
    }

    public function testDuplicateIsRejected(): void
    {
        $registry = new Registry();
        $registry->register(new TextType());

        $this->expectException(InvalidArgumentException::class);
        $registry->register(new TextType());
    }

    public function testSanitizeSectionsPreservesUnknownTypesAndDropsInvalidEntries(): void
    {
        $registry = new Registry();
        $registry->register(new TextType());
        $unknown = ['id' => 'u1', 'type' => 'hero', 'data' => ['x' => '<i>raw</i>']];

        $clean = $registry->sanitizeSections([
            ['id' => 'a', 'type' => 'text', 'data' => ['body' => '<i>H</i>']],
            $unknown,
            ['type' => 'text'],
            'garbage',
        ]);

        $this->assertSame(
            [['id' => 'a', 'type' => 'text', 'data' => ['body' => 'H']], $unknown],
            $clean,
        );
    }
}
