<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Backgrounds;
use Sectionist\Tests\Support\WpState;

final class BackgroundsTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        WpState::reset();
        $this->dir = sys_get_temp_dir().'/sectionist-bg-'.uniqid();
        mkdir($this->dir);
        WpState::$themeDir = $this->dir;
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir.'/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->dir);
    }

    private function config(string $json): void
    {
        file_put_contents($this->dir.'/sectionist.json', $json);
    }

    public function test_reads_backgrounds_from_config(): void
    {
        $this->config('{"backgrounds":[{"key":"Light-Gray","label":"Light gray"},{"key":""},"x"]}');

        $this->assertSame([['key' => 'light-gray', 'label' => 'Light gray']], Backgrounds::all());
    }

    public function test_missing_config_yields_nothing(): void
    {
        $this->assertSame([], Backgrounds::all());
        $this->assertSame([], Backgrounds::editorStyles());
    }

    public function test_broken_json_yields_nothing(): void
    {
        $this->config('{not json');

        $this->assertSame([], Backgrounds::all());
        $this->assertSame([], Backgrounds::editorStyles());
    }

    public function test_editor_styles_accept_local_files_and_urls_only(): void
    {
        file_put_contents($this->dir.'/theme.css', '');
        $this->config('{"editor_styles":["theme.css","missing.css","../evil.css","https://cdn.test/a.css",5]}');

        $styles = Backgrounds::editorStyles();

        $this->assertCount(2, $styles);
        $this->assertSame('https://example.test/theme/theme.css', $styles[0]['url']);
        $this->assertSame((string) filemtime($this->dir.'/theme.css'), $styles[0]['version']);
        $this->assertSame(['url' => 'https://cdn.test/a.css', 'version' => null], $styles[1]);
    }
}
