<?php

declare(strict_types=1);

namespace Sectionist;

use Sectionist\Admin\Assets;
use Sectionist\Admin\MetaBox;
use Sectionist\Admin\SaveHandler;
use Sectionist\Migration\Runner;
use Sectionist\Render\Renderer;
use Sectionist\Render\ViewResolver;
use Sectionist\Rest\PreviewController;
use Sectionist\Sections\Registry;
use Sectionist\Sections\Types\HeroType;
use Sectionist\Sections\Types\TextType;
use Sectionist\Storage\Document;
use Sectionist\Storage\PostContentRepository;

final class Plugin
{
    private static ?Renderer $renderer = null;

    public static function boot(): void
    {
        add_action(Registry::HOOK, static function (Registry $registry): void {
            $registry->register(new TextType);
            $registry->register(new HeroType);
        });

        $registry = new Registry;
        $repository = new PostContentRepository;

        new MetaBox($registry, $repository)->register();
        new SaveHandler($registry, $repository)->register();
        new Assets()->register();

        self::$renderer = $renderer = new Renderer($registry, $repository, ViewResolver::forWordPress(dirname(__DIR__)));
        new PreviewController($registry, $renderer)->register();

        new Runner(Document::CURRENT_VERSION)->register();
    }

    /**
     * Renders all sections of a post in editor order. Defaults to the current post.
     */
    public static function render(?int $postId = null): string
    {
        $postId ??= (int) get_the_ID();

        return self::$renderer?->renderPost($postId) ?? '';
    }
}
