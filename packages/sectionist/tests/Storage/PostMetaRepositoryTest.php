<?php

declare(strict_types=1);

namespace Sectionist\Tests\Storage;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Storage\Document;
use Sectionist\Storage\PostMetaRepository;
use Sectionist\Tests\Support\WpState;

#[CoversClass(PostMetaRepository::class)]
#[CoversClass(Document::class)]
final class PostMetaRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        WpState::reset();
    }

    public function testRoundTripKeepsBackslashesAndUnknownTypes(): void
    {
        $sections = [
            ['id' => 'a', 'type' => 'text', 'data' => ['body' => 'C:\\path "quoted"']],
            ['id' => 'b', 'type' => 'hero', 'data' => ['x' => 1]],
        ];
        $repo = new PostMetaRepository();

        $this->assertTrue($repo->save(5, new Document($sections)));

        $loaded = $repo->load(5);
        $this->assertSame($sections, $loaded->sections);
        $this->assertSame(1, $loaded->schemaVersion);
    }

    public function testNewerSchemaVersionIsNeverOverwritten(): void
    {
        WpState::$meta['5:_sectionist_data'] = '{"schema_version":99,"sections":[]}';
        $repo = new PostMetaRepository();

        $this->assertFalse($repo->save(5, new Document([['id' => 'a', 'type' => 'text', 'data' => []]])));
        $this->assertSame('{"schema_version":99,"sections":[]}', WpState::$meta['5:_sectionist_data']);
    }

    public function testEmptyOrInvalidJsonYieldsEmptyDocument(): void
    {
        $repo = new PostMetaRepository();
        $this->assertSame([], $repo->load(1)->sections);

        WpState::$meta['1:_sectionist_data'] = '{broken';
        $this->assertSame([], $repo->load(1)->sections);
    }
}
