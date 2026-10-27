<?php

declare(strict_types=1);

namespace Sectionist\Tests\Render;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Render\Renderer;
use Sectionist\Render\ViewResolver;
use Sectionist\Sections\Registry;
use Sectionist\Sections\Types\EmbedType;
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
        $registry->register(new EmbedType);
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

    public function test_renders_embed_fallback_as_link_with_caption(): void
    {
        $html = $this->renderer->renderSection(new Section('a', 'embed', data: [
            'url' => 'https://www.youtube.com/watch?v=x',
            'caption' => 'A video',
        ]));

        $this->assertStringContainsString('href="https://www.youtube.com/watch?v=x"', $html);
        $this->assertStringContainsString('<figcaption>A video</figcaption>', $html);
        $this->assertStringContainsString('sectionist-section sectionist-section--embed', $html);
        $this->assertStringContainsString('sectionist-section__content', $html);
        $this->assertStringNotContainsString('<iframe', $html);
    }

    public function test_embed_without_content_renders_nothing(): void
    {
        $this->assertSame('', $this->renderer->renderSection(new Section('a', 'embed')));
    }

    public function test_renders_embed_body_without_link(): void
    {
        $html = $this->renderer->renderSection(new Section('a', 'embed', data: [
            'body' => '<p>Only text</p>',
        ]));

        $this->assertStringContainsString('<p>Only text</p>', $html);
        $this->assertStringContainsString('sectionist-section sectionist-section--embed', $html);
        $this->assertStringContainsString('sectionist-section__content', $html);
        $this->assertStringNotContainsString('<a ', $html);
        $this->assertStringNotContainsString('target="_blank"', $html);
    }

    public function test_renders_embed_body_link_and_cta(): void
    {
        $html = $this->renderer->renderSection(new Section('a', 'embed', data: [
            'body' => '<p>Intro</p>',
            'url' => 'https://example.test/video',
            'cta' => ['active' => true, 'label' => 'Watch', 'url' => 'https://example.test/watch'],
        ]));

        $this->assertStringContainsString('<p>Intro</p>', $html);
        $this->assertStringContainsString('sectionist-section sectionist-section--embed', $html);
        $this->assertStringContainsString('sectionist-section__content', $html);
        $this->assertStringContainsString('href="https://example.test/video"', $html);
        $this->assertStringContainsString('class="sectionist-cta"', $html);
        $this->assertStringContainsString('>Watch</a>', $html);
        $this->assertStringNotContainsString('<iframe', $html);
    }

    public function test_renders_embed_cta_without_url_or_body(): void
    {
        $html = $this->renderer->renderSection(new Section('a', 'embed', data: [
            'cta' => ['active' => true, 'label' => 'Watch', 'url' => 'https://example.test/watch'],
        ]));

        $this->assertStringContainsString('class="sectionist-cta"', $html);
        $this->assertStringContainsString('sectionist-section sectionist-section--embed', $html);
        $this->assertStringContainsString('sectionist-section__content', $html);
        $this->assertStringNotContainsString('<figure', $html);
    }

    public function test_embed_with_active_cta_without_target_renders_nothing(): void
    {
        $this->assertSame('', $this->renderer->renderSection(new Section('a', 'embed', data: [
            'cta' => ['active' => true, 'label' => 'Watch'],
        ])));
    }

    public function test_embed_caption_is_escaped(): void
    {
        $html = $this->renderer->renderSection(new Section('a', 'embed', data: [
            'url' => 'https://example.test/video',
            'caption' => '<script>alert(1)</script>',
        ]));

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('sectionist-section sectionist-section--embed', $html);
        $this->assertStringContainsString('sectionist-section__content', $html);
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
