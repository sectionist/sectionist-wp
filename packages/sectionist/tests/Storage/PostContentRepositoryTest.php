<?php

declare(strict_types=1);

namespace Sectionist\Tests\Storage;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Storage\Document;
use Sectionist\Storage\PostContentRepository;
use Sectionist\Storage\Section;
use Sectionist\Tests\Support\WpState;

#[CoversClass(PostContentRepository::class)]
final class PostContentRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        WpState::reset();
    }

    public function test_encode_load_round_trip(): void
    {
        $sections = [new Section('a', 'hero', data: ['x' => 'C:\\path "q"'])];
        $repo = new PostContentRepository;

        $json = $repo->encode(5, new Document($sections));
        WpState::$content[5] = (string) $json;

        $this->assertEquals($sections, $repo->load(5)->sections);
    }

    public function test_encode_refuses_newer_schema_version(): void
    {
        WpState::$content[5] = '{"schema_version":99,"sections":[]}';

        $this->assertNull((new PostContentRepository)->encode(5, new Document([])));
    }

    public function test_empty_or_invalid_content_yields_empty_document(): void
    {
        $repo = new PostContentRepository;
        $this->assertSame([], $repo->load(0)->sections);

        WpState::$content[1] = '{broken';
        $this->assertSame([], $repo->load(1)->sections);
    }
}
