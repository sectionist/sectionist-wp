<?php

declare(strict_types=1);

namespace Sectionist\Admin;

use Sectionist\Faq\Faq;
use Sectionist\Sections\Backgrounds;

final class Assets
{
    private const SCRIPT = 'dist/editor.js';

    private const STYLE = 'dist/editor.css';

    public function register(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('admin_notices', [$this, 'notice']);
        add_filter('admin_body_class', [$this, 'bodyClass']);
    }

    public function bodyClass(string $classes): string
    {
        return $this->isEditorScreen() ? trim($classes.' sectionist-screen') : $classes;
    }

    public function enqueue(): void
    {
        if (! $this->isEditorScreen() || ! $this->isBuilt()) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script('sectionist-editor', $this->url(self::SCRIPT), [], (string) filemtime($this->path(self::SCRIPT)), true);
        if (is_file($this->path(self::STYLE))) {
            wp_enqueue_style('sectionist-editor', $this->url(self::STYLE), [], (string) filemtime($this->path(self::STYLE)));
        }

        foreach (Backgrounds::editorStyles() as $index => $style) {
            wp_enqueue_style('sectionist-theme-'.$index, $style['url'], [], $style['version']);
        }
    }

    public function notice(): void
    {
        if (! $this->isEditorScreen() || $this->isBuilt()) {
            return;
        }

        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html__('Sectionist: the editor assets are missing. Run "npm install && npm run build" in the plugin folder.', 'sectionist'),
        );
    }

    private function isEditorScreen(): bool
    {
        $screen = get_current_screen();

        return $screen !== null && in_array($screen->post_type, [...MetaBox::postTypes(), Faq::POST_TYPE], true);
    }

    private function isBuilt(): bool
    {
        return is_file($this->path(self::SCRIPT));
    }

    private function path(string $relative): string
    {
        return dirname(__DIR__, 2).'/'.$relative;
    }

    private function url(string $relative): string
    {
        return plugins_url($relative, dirname(__DIR__, 2).'/sectionist.php');
    }
}
