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
        $this->base = sys_get_temp_dir() . '/sectionist-' . uniqid();
        foreach (['child', 'parent', 'plugin'] as $dir) {
            mkdir($this->base . '/' . $dir . '/sectionist', 0777, true);
        }
        WpState::$childDir = $this->base . '/child';
        WpState::$parentDir = $this->base . '/parent';
    }

    protected function tearDown(): void
    {
        foreach (glob($this->base . '/*/sectionist/*.php') ?: [] as $file) {
            unlink($file);
        }
        foreach (['child', 'parent', 'plugin'] as $dir) {
            rmdir($this->base . '/' . $dir . '/sectionist');
            rmdir($this->base . '/' . $dir);
        }
        rmdir($this->base);
    }

    private function view(string $root, string $type): string
    {
        $file = $this->base . '/' . $root . '/sectionist/' . $type . '.php';
        file_put_contents($file, '<?php');

        return $file;
    }

    private function resolver(): ViewResolver
    {
        // The plugin fallback lives in "views/"; reuse the temp dir layout for it.
        return new ViewResolver([
            $this->base . '/child/sectionist',
            $this->base . '/parent/sectionist',
            $this->base . '/plugin/sectionist',
        ]);
    }

    public function testChildThemeWinsOverParentAndPlugin(): void
    {
        $child = $this->view('child', 'text');
        $this->view('parent', 'text');
        $this->view('plugin', 'text');

        $this->assertSame($child, $this->resolver()->resolve('text'));
    }

    public function testParentThemeWinsOverPlugin(): void
    {
        $parent = $this->view('parent', 'text');
        $this->view('plugin', 'text');

        $this->assertSame($parent, $this->resolver()->resolve('text'));
    }

    public function testFallsBackToPlugin(): void
    {
        $plugin = $this->view('plugin', 'text');

        $this->assertSame($plugin, $this->resolver()->resolve('text'));
    }

    public function testReturnsNullWhenNoViewExists(): void
    {
        $this->assertNull($this->resolver()->resolve('missing'));
    }

    public function testRejectsUnsafeTypeNames(): void
    {
        $this->view('plugin', 'text');

        $this->assertNull($this->resolver()->resolve('../text'));
        $this->assertNull($this->resolver()->resolve(''));
    }

    public function testForWordPressOrdersChildParentPlugin(): void
    {
        $this->view('plugin', 'text');
        mkdir($this->base . '/plugin/views');
        $fallback = $this->base . '/plugin/views/text.php';
        file_put_contents($fallback, '<?php');
        $parent = $this->view('parent', 'text');

        $resolver = ViewResolver::forWordPress($this->base . '/plugin');
        $this->assertSame($parent, $resolver->resolve('text'));

        unlink($parent);
        $this->assertSame($fallback, $resolver->resolve('text'));

        unlink($fallback);
        rmdir($this->base . '/plugin/views');
    }
}
