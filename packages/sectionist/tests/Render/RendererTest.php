<?php

declare(strict_types=1);

namespace Sectionist\Tests\Render;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Render\Renderer;
use Sectionist\Render\ViewResolver;
use Sectionist\Sections\Registry;
use Sectionist\Sections\Types\TextType;
use Sectionist\Storage\Document;
use Sectionist\Storage\PostMetaRepository;
use Sectionist\Tests\Support\WpState;

#[CoversClass(Renderer::class)]
final class RendererTest extends TestCase
{
    private Renderer $renderer;

    private PostMetaRepository $repository;

    protected function setUp(): void
    {
        WpState::reset();
        $registry = new Registry();
        $registry->register(new TextType());
        $this->repository = new PostMetaRepository();
        $this->renderer = new Renderer($registry, $this->repository, new ViewResolver([dirname(__DIR__, 2) . '/views']));
    }

    public function testRendersTextSectionBody(): void
    {
        $html = $this->renderer->renderSection([
            'id' => 'a',
            'type' => 'text',
            'data' => ['body' => '<h2>Title</h2><p>Hello</p>'],
        ]);

        $this->assertStringContainsString('<h2>Title</h2>', $html);
        $this->assertStringContainsString('<p>Hello</p>', $html);
    }

    public function testMissingFieldsGetDefaults(): void
    {
        $html = $this->renderer->renderSection(['id' => 'a', 'type' => 'text', 'data' => []]);

        $this->assertStringNotContainsString('<h2>', $html);
        $this->assertStringContainsString('sectionist-section--text', $html);
    }

    public function testInactiveSectionRendersNothing(): void
    {
        $this->assertSame('', $this->renderer->renderSection(['id' => 'a', 'type' => 'text', 'active' => false, 'data' => []]));
    }

    public function testUnknownTypeRendersNothing(): void
    {
        $this->assertSame('', $this->renderer->renderSection(['id' => 'a', 'type' => 'custom', 'data' => []]));
        $this->assertSame('', $this->renderer->renderSection(['id' => 'a']));
    }

    public function testRenderPostUsesStoredSections(): void
    {
        $this->repository->save(5, new Document([['id' => 'a', 'type' => 'text', 'data' => ['body' => '<p>Stored</p>']]]));

        $this->assertStringContainsString('Stored', $this->renderer->renderPost(5));
        $this->assertSame('', $this->renderer->renderPost(6));
    }

    public function testRenderPostRendersNothingForNewerSchemaVersion(): void
    {
        $this->repository->save(5, new Document([['id' => 'a', 'type' => 'text', 'data' => ['body' => '<p>Stored</p>']]]));
        WpState::$meta['5:_sectionist_data'] = '{"schema_version":99,"sections":[{"id":"a","type":"text","data":{"body":"X"}}]}';

        $this->assertSame('', $this->renderer->renderPost(5));
    }
}
