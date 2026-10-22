<?php

declare(strict_types=1);

namespace Sectionist\Tests\Storage;

use PHPUnit\Framework\TestCase;
use Sectionist\Storage\Document;

final class DocumentTest extends TestCase
{
    public function test_active_sections_skips_only_explicitly_inactive(): void
    {
        $document = new Document([
            ['id' => 'a', 'type' => 'text'],
            ['id' => 'b', 'type' => 'text', 'active' => false],
            ['id' => 'c', 'type' => 'text', 'active' => true],
        ]);

        $this->assertSame(['a', 'c'], array_column($document->activeSections(), 'id'));
    }
}
