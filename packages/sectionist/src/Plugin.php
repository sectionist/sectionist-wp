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
use Sectionist\Sections\Types\TextType;
use Sectionist\Storage\Document;
use Sectionist\Storage\PostMetaRepository;
use Sectionist\Storage\Revisions;

final class Plugin
{
    public static function boot(): void
    {
        add_action(Registry::HOOK, static function (Registry $registry): void {
            $registry->register(new TextType());
        });

        $registry = new Registry();
        $repository = new PostMetaRepository();

        new MetaBox($registry, $repository)->register();
        new SaveHandler($registry, $repository)->register();
        (new Assets())->register();
        $renderer = new Renderer($registry, $repository, ViewResolver::forWordPress(dirname(__DIR__)));
        new PreviewController($registry, $renderer)->register();
        (new Revisions())->register();
        new Runner(Document::CURRENT_VERSION)->register();
    }
}
