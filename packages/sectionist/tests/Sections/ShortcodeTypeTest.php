<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Field;
use Sectionist\Sections\Types\ShortcodeType;

#[CoversClass(ShortcodeType::class)]
#[CoversClass(Field::class)]
final class ShortcodeTypeTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['sectionist_test_shortcodes'] = ['contact-form-7', 'gallery'];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['sectionist_test_shortcodes']);
    }

    public function test_defaults_have_empty_list(): void
    {
        $this->assertSame([], (new ShortcodeType)->defaults()['shortcodes']);
    }

    public function test_keeps_registered_single_shortcodes(): void
    {
        $clean = (new ShortcodeType)->sanitize(['shortcodes' => [
            ['code' => ' [contact-form-7 id="12" title="Contact"] '],
            ['code' => '[gallery]'],
        ]]);

        $this->assertSame([['code' => '[contact-form-7 id="12" title="Contact"]'], ['code' => '[gallery]']], $clean['shortcodes']);
    }

    public function test_drops_unregistered_multiple_and_surrounded_shortcodes(): void
    {
        $clean = (new ShortcodeType)->sanitize(['shortcodes' => [
            ['code' => '[unknown]'],
            ['code' => '[gallery][gallery]'],
            ['code' => 'text [gallery]'],
            ['code' => '[gallery]text'],
            ['code' => ''],
            'nope',
        ]]);

        $this->assertSame([], $clean['shortcodes']);
    }

    public function test_invalid_value_becomes_empty_list(): void
    {
        $this->assertSame([], (new ShortcodeType)->sanitize(['shortcodes' => 'x'])['shortcodes']);
    }

    public function test_list_is_capped(): void
    {
        $many = array_fill(0, Field::LIST_LIMIT + 10, ['code' => '[gallery]']);

        $this->assertCount(Field::LIST_LIMIT, (new ShortcodeType)->sanitize(['shortcodes' => $many])['shortcodes']);
    }
}
