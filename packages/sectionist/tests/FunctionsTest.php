<?php

declare(strict_types=1);

namespace Sectionist\Tests;

use PHPUnit\Framework\TestCase;
use Sectionist\Plugin;
use Sectionist\Tests\Support\WpState;

final class FunctionsTest extends TestCase
{
    private const CONTENT = '{"schema_version":1,"sections":[{"id":"a","type":"text","data":{"body":"<p>First</p>"}},{"id":"b","type":"text","data":{"body":"<p>Second</p>"}}]}';

    protected function setUp(): void
    {
        WpState::reset();
        require_once dirname(__DIR__).'/src/functions.php';
        Plugin::boot();
    }

    private function render(?int $postId = null): string
    {
        ob_start();
        sectionist_render($postId);

        return (string) ob_get_clean();
    }

    public function test_renders_given_post_in_editor_order(): void
    {
        WpState::$content[7] = self::CONTENT;

        $html = $this->render(7);

        $this->assertStringContainsString('<p>First</p>', $html);
        $this->assertLessThan(strpos($html, '<p>Second</p>'), strpos($html, '<p>First</p>'));
    }

    public function test_defaults_to_current_post(): void
    {
        WpState::$content[9] = self::CONTENT;
        WpState::$currentPostId = 9;

        $this->assertStringContainsString('<p>First</p>', $this->render());
    }

    public function test_post_without_content_renders_nothing(): void
    {
        $this->assertSame('', $this->render(123));
    }
}
