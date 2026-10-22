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
use Sectionist\Storage\PostContentRepository;
use Sectionist\Storage\Section;
use Sectionist\Tests\Support\WpState;

#[CoversClass(Renderer::class)]
final class RendererTest extends TestCase
{
    private Renderer $renderer;

    private PostContentRepository $repository;

    protected function setUp(): void
    {
        WpState::reset();
        $registry = new Registry;
        $registry->register(new TextType);
        $this->repository = new PostContentRepository;
        $this->renderer = new Renderer($registry, $this->repository, new ViewResolver([dirname(__DIR__, 2).'/views']));
    }

    public function test_renders_text_section_body(): void
    {
        $html = $this->renderer->renderSection(new Section('a', 'text', data: ['body' => '<h2>Title</h2><p>Hello</p>']));

        $this->assertStringContainsString('<h2>Title</h2>', $html);
        $this->assertStringContainsString('<p>Hello</p>', $html);
    }

    public function test_missing_fields_get_defaults(): void
    {
        $html = $this->renderer->renderSection(new Section('a', 'text'));

        $this->assertStringNotContainsString('<h2>', $html);
        $this->assertStringContainsString('sectionist-section--text', $html);
    }

    public function test_inactive_section_renders_nothing(): void
    {
        $this->assertSame('', $this->renderer->renderSection(new Section('a', 'text', active: false)));
    }

    public function test_unknown_type_renders_nothing(): void
    {
        $this->assertSame('', $this->renderer->renderSection(new Section('a', 'custom')));
        $this->assertSame('', $this->renderer->renderSection(new Section('a', '')));
    }

    public function test_render_post_uses_stored_sections(): void
    {
        WpState::$content[5] = (string) json_encode(new Document([new Section('a', 'text', data: ['body' => '<p>Stored</p>'])])->toArray());

        $this->assertStringContainsString('Stored', $this->renderer->renderPost(5));
        $this->assertSame('', $this->renderer->renderPost(6));
    }

    public function test_render_post_renders_nothing_for_newer_schema_version(): void
    {
        WpState::$content[5] = '{"schema_version":99,"sections":[{"id":"a","type":"text","data":{"body":"X"}}]}';

        $this->assertSame('', $this->renderer->renderPost(5));
    }
}
