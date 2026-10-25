<?php

declare(strict_types=1);

namespace Sectionist\Tests\Render;

use PHPUnit\Framework\TestCase;
use Sectionist\Render\Excerpt;
use Sectionist\Storage\Document;

final class ExcerptTest extends TestCase
{
    public function test_text_joins_active_rich_text_without_tags(): void
    {
        $document = Document::fromJson((string) json_encode(['sections' => [
            ['id' => 'a', 'type' => 'text', 'data' => ['body' => '<h2>Title</h2><p>First</p>']],
            ['id' => 'b', 'type' => 'text', 'active' => false, 'data' => ['body' => '<p>Hidden</p>']],
            ['id' => 'c', 'type' => 'text', 'data' => ['body' => '<p>Second</p>']],
        ]]));

        $this->assertSame('TitleFirst Second', Excerpt::text($document));
    }

    public function test_text_of_empty_document_is_empty(): void
    {
        $this->assertSame('', Excerpt::text(new Document));
    }
}
