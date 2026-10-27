<?php

declare(strict_types=1);

namespace Sectionist\Tests\Starter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Render\Renderer;
use Sectionist\Render\ViewResolver;
use Sectionist\Sections\Registry;
use Sectionist\Sections\Types\EmbedType;
use Sectionist\Storage\PostContentRepository;
use Sectionist\Storage\Section;
use Sectionist\Tests\Support\WpState;

/**
 * Renders the starter theme's embed view through the real renderer.
 */
#[CoversClass(Renderer::class)]
final class EmbedViewTest extends TestCase
{
    private const URL = 'https://www.youtube.com/watch?v=x';

    private Renderer $renderer;

    protected function setUp(): void
    {
        WpState::reset();
        $registry = new Registry;
        $registry->register(new EmbedType);
        $this->renderer = new Renderer(
            $registry,
            new PostContentRepository,
            new ViewResolver([dirname(__DIR__, 3).'/sectionist-starter/sections']),
        );
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['wp_embed']);
    }

    public function test_renders_body_and_cta(): void
    {
        $html = $this->renderer->renderSection(new Section('a', 'embed', data: [
            'body' => '<p>Intro</p>',
            'cta' => ['active' => true, 'label' => 'Watch', 'url' => 'https://example.test/watch'],
        ]));

        $this->assertStringContainsString('section section--embed', $html);
        $this->assertStringContainsString('<p>Intro</p>', $html);
        $this->assertStringContainsString('class="section-cta"', $html);
        $this->assertStringContainsString('>Watch</a>', $html);
    }

    public function test_background_class_follows_the_backdrop_choice(): void
    {
        $withColor = $this->renderer->renderSection(new Section('a', 'embed', background: 'light-gray', data: [
            'body' => '<p>x</p>',
            'media' => 'background',
        ]));
        $withoutColor = $this->renderer->renderSection(new Section('a', 'embed', background: 'light-gray', data: [
            'body' => '<p>x</p>',
            'media' => 'none',
        ]));

        $this->assertStringContainsString('section section--embed section--bg-light-gray', $withColor);
        $this->assertStringContainsString('section section--embed', $withoutColor);
        $this->assertStringNotContainsString('section--bg-', $withoutColor);
    }

    public function test_consent_gate_templates_an_https_iframe(): void
    {
        $this->stubProviderHtml('<iframe src="https://www.youtube.com/embed/x" title="Video" width="560" height="315"></iframe>');

        $html = $this->renderer->renderSection(new Section('a', 'embed', data: ['url' => self::URL]));

        $this->assertStringContainsString('This embed loads content from www.youtube.com.', $html);
        $this->assertStringContainsString('<template data-sectionist-embed-template>', $html);
        $this->assertMatchesRegularExpression(
            '#<template data-sectionist-embed-template><iframe loading="lazy" src="https://www\.youtube\.com/embed/x"#',
            $html,
        );

        $position = strpos($html, '<template');
        $this->assertIsInt($position);
        $this->assertStringNotContainsString('<iframe', substr($html, 0, $position));
    }

    public function test_http_iframe_falls_back_to_the_provider_link(): void
    {
        $this->stubProviderHtml('<iframe src="http://www.youtube.com/embed/x"></iframe>');

        $html = $this->renderer->renderSection(new Section('a', 'embed', data: ['url' => self::URL]));

        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertStringNotContainsString('<template', $html);
        $this->assertStringContainsString('>'.self::URL.'</a>', $html);
    }

    public function test_provider_output_without_iframe_renders_the_link(): void
    {
        $this->stubProviderHtml('<a href="https://example.test/embed">Embed</a>');

        $html = $this->renderer->renderSection(new Section('a', 'embed', data: ['url' => self::URL]));

        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertStringNotContainsString('<template', $html);
        $this->assertStringContainsString('>'.self::URL.'</a>', $html);
    }

    public function test_empty_section_renders_nothing(): void
    {
        $this->assertSame('', $this->renderer->renderSection(new Section('a', 'embed')));
    }

    private function stubProviderHtml(string $html): void
    {
        $GLOBALS['wp_embed'] = new class($html) extends \WP_Embed
        {
            public function __construct(private readonly string $html) {}

            public function shortcode(array $attr, string $url = ''): string
            {
                return $this->html;
            }
        };
    }
}
