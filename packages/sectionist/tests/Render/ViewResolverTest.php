<?php

declare(strict_types=1);

namespace Sectionist\Tests\Render;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Render\ViewResolver;
use Sectionist\Tests\Support\WpState;

#[CoversClass(ViewResolver::class)]
final class ViewResolverTest extends TestCase
{
    private string $base;

    protected function setUp(): void
    {
        WpState::reset();
        $this->base = sys_get_temp_dir().'/sectionist-'.uniqid();
        foreach (['theme', 'plugin'] as $dir) {
            mkdir($this->base.'/'.$dir.'/sections', 0777, true);
        }
        WpState::$themeDir = $this->base.'/theme';
    }

    protected function tearDown(): void
    {
        foreach (glob($this->base.'/*/sections/*.php') ?: [] as $file) {
            unlink($file);
        }
        foreach (['theme', 'plugin'] as $dir) {
            rmdir($this->base.'/'.$dir.'/sections');
            rmdir($this->base.'/'.$dir);
        }
        rmdir($this->base);
    }

    private function view(string $root, string $type): string
    {
        $file = $this->base.'/'.$root.'/sections/'.$type.'.php';
        file_put_contents($file, '<?php');

        return $file;
    }

    private function resolver(): ViewResolver
    {
        // The plugin fallback lives in "views/"; reuse the temp dir layout for it.
        return new ViewResolver([
            $this->base.'/theme/sections',
            $this->base.'/plugin/sections',
        ]);
    }

    public function test_theme_wins_over_plugin(): void
    {
        $theme = $this->view('theme', 'text');
        $this->view('plugin', 'text');

        $this->assertSame($theme, $this->resolver()->resolve('text'));
    }

    public function test_falls_back_to_plugin(): void
    {
        $plugin = $this->view('plugin', 'text');

        $this->assertSame($plugin, $this->resolver()->resolve('text'));
    }

    public function test_returns_null_when_no_view_exists(): void
    {
        $this->assertNull($this->resolver()->resolve('missing'));
    }

    public function test_rejects_unsafe_type_names(): void
    {
        $this->view('plugin', 'text');

        $this->assertNull($this->resolver()->resolve('../text'));
        $this->assertNull($this->resolver()->resolve(''));
    }

    public function test_for_word_press_orders_theme_plugin(): void
    {
        $this->view('plugin', 'text');
        mkdir($this->base.'/plugin/views');
        $fallback = $this->base.'/plugin/views/text.php';
        file_put_contents($fallback, '<?php');
        $theme = $this->view('theme', 'text');

        $resolver = ViewResolver::forWordPress($this->base.'/plugin');
        $this->assertSame($theme, $resolver->resolve('text'));

        unlink($theme);
        $this->assertSame($fallback, $resolver->resolve('text'));

        unlink($fallback);
        rmdir($this->base.'/plugin/views');
    }
}
