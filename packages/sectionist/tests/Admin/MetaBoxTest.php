<?php

declare(strict_types=1);

namespace Sectionist\Tests\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Admin\MetaBox;
use Sectionist\Sections\Registry;
use Sectionist\Storage\PostContentRepository;
use Sectionist\Tests\Support\WpState;
use WP_Screen;

#[CoversClass(MetaBox::class)]
final class MetaBoxTest extends TestCase
{
    private MetaBox $metaBox;

    protected function setUp(): void
    {
        WpState::reset();
        $this->metaBox = new MetaBox(new Registry, new PostContentRepository);
    }

    private function notice(): string
    {
        ob_start();
        $this->metaBox->warnAboutOtherEditor();

        return (string) ob_get_clean();
    }

    private function screen(string $postType): void
    {
        $screen = new WP_Screen;
        $screen->base = 'post';
        $screen->post_type = $postType;
        WpState::$screen = $screen;
    }

    public function test_warns_when_another_plugin_enabled_the_editor(): void
    {
        $this->screen(MetaBox::postTypes()[0]);
        WpState::$editorSupport = true;

        $this->assertStringContainsString('notice-warning', $this->notice());
    }

    public function test_silent_when_editor_is_disabled(): void
    {
        $this->screen(MetaBox::postTypes()[0]);

        $this->assertSame('', $this->notice());
    }

    public function test_silent_for_other_post_types_and_without_screen(): void
    {
        WpState::$editorSupport = true;
        $this->assertSame('', $this->notice());

        $this->screen('revision');
        $this->assertSame('', $this->notice());
    }
}
