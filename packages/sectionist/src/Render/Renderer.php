<?php

declare(strict_types=1);

namespace Sectionist\Render;

use Sectionist\Sections\Registry;
use Sectionist\Storage\Repository;
use Sectionist\Storage\Section;

final class Renderer
{
    public function __construct(
        private readonly Registry $registry,
        private readonly Repository $repository,
        private readonly ViewResolver $resolver,
    ) {}

    /**
     * Renders all sections stored for a post. Data in, HTML out.
     */
    public function renderPost(int $postId): string
    {
        $document = $this->repository->load($postId);

        return $document->isNewerThanSupported() ? '' : $this->renderSections($document->activeSections());
    }

    /**
     * Sections of unknown types, without a view or marked inactive render as empty strings.
     *
     * @param  list<Section>  $sections
     */
    public function renderSections(array $sections): string
    {
        $html = '';
        foreach ($sections as $section) {
            $html .= $this->renderSection($section);
        }

        return $html;
    }

    public function renderSection(Section $section): string
    {
        if (! $section->active) {
            return '';
        }

        $type = $this->registry->get($section->type);
        $file = $this->resolver->resolve($section->type);
        if ($type === null || $file === null) {
            return '';
        }

        $data = array_merge($type->defaults(), $section->data);

        return (static function (string $__file, array $data, Section $section): string {
            ob_start();
            include $__file;

            return (string) ob_get_clean();
        })($file, $data, $section);
    }
}
