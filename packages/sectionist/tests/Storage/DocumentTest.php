<?php

declare(strict_types=1);

namespace Sectionist\Tests\Storage;

use PHPUnit\Framework\TestCase;
use Sectionist\Storage\Document;
use Sectionist\Storage\Section;

final class DocumentTest extends TestCase
{
    public function test_active_sections_skips_only_explicitly_inactive(): void
    {
        $document = new Document([
            new Section('a', 'text'),
            new Section('b', 'text', active: false),
            new Section('c', 'text', active: true),
        ]);

        $this->assertSame(['a', 'c'], array_map(static fn (Section $s): string => $s->id, $document->activeSections()));
    }
}
