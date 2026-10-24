<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Field;
use Sectionist\Sections\Types\ListType;

#[CoversClass(ListType::class)]
#[CoversClass(Field::class)]
final class ListTypeTest extends TestCase
{
    public function test_defaults_have_empty_list(): void
    {
        $this->assertSame([], (new ListType)->defaults()['entries']);
    }

    public function test_keeps_entries_and_drops_empty_ones(): void
    {
        $clean = (new ListType)->sanitize(['entries' => [
            ['title' => 'A', 'text' => 'one'],
            ['title' => '', 'text' => ''],
            'nope',
            ['title' => 'B'],
        ]]);

        $this->assertSame([['title' => 'A', 'text' => 'one'], ['title' => 'B', 'text' => '']], $clean['entries']);
    }

    public function test_invalid_value_becomes_empty_list(): void
    {
        $this->assertSame([], (new ListType)->sanitize(['entries' => 'x'])['entries']);
    }

    public function test_list_is_capped(): void
    {
        $many = array_fill(0, Field::LIST_LIMIT + 10, ['title' => 'T', 'text' => '']);

        $this->assertCount(Field::LIST_LIMIT, (new ListType)->sanitize(['entries' => $many])['entries']);
    }
}
