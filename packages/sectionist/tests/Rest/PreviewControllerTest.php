<?php

declare(strict_types=1);

namespace Sectionist\Tests\Rest;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Render\Renderer;
use Sectionist\Render\ViewResolver;
use Sectionist\Rest\PreviewController;
use Sectionist\Sections\Registry;
use Sectionist\Sections\Types\TextType;
use Sectionist\Storage\PostMetaRepository;
use Sectionist\Tests\Support\WpState;
use WP_REST_Request;

#[CoversClass(PreviewController::class)]
final class PreviewControllerTest extends TestCase
{
    private PreviewController $controller;

    protected function setUp(): void
    {
        WpState::reset();
        $registry = new Registry();
        $registry->register(new TextType());
        $renderer = new Renderer($registry, new PostMetaRepository(), new ViewResolver([dirname(__DIR__, 2) . '/views']));
        $this->controller = new PreviewController($registry, $renderer);
    }

    public function testPermissionFollowsEditCapability(): void
    {
        $request = new WP_REST_Request(['post_id' => 3]);

        $this->assertTrue($this->controller->permission($request));

        WpState::$canEdit = false;
        $this->assertFalse($this->controller->permission($request));
    }

    public function testPreviewSanitizesBeforeRendering(): void
    {
        $request = new WP_REST_Request(['post_id' => 3, 'sections' => [
            ['id' => 'a', 'type' => 'text', 'data' => ['heading' => 'Hi', 'body' => '<script>x</script><p>ok</p>']],
        ]]);

        $html = $this->controller->handle($request)['html'];

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('<p>ok</p>', $html);
    }

    public function testInvalidSectionsParamGivesEmptyHtml(): void
    {
        $this->assertSame('', $this->controller->handle(new WP_REST_Request(['sections' => 'nope']))['html']);
    }
}
