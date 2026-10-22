<?php

declare(strict_types=1);

namespace Sectionist\Render;

use Sectionist\Sections\Registry;
use Sectionist\Storage\Repository;

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
     * @param  list<array<string, mixed>>  $sections
     */
    public function renderSections(array $sections): string
    {
        $html = '';
        foreach ($sections as $section) {
            $html .= $this->renderSection($section);
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $section
     */
    public function renderSection(array $section): string
    {
        $name = $section['type'] ?? null;
        if (! is_string($name) || ($section['active'] ?? true) === false) {
            return '';
        }

        $type = $this->registry->get($name);
        $file = $this->resolver->resolve($name);
        if ($type === null || $file === null) {
            return '';
        }

        $stored = is_array($section['data'] ?? null) ? $section['data'] : [];
        $data = array_merge($type->defaults(), $stored);

        return (static function (string $__file, array $data, array $section): string {
            ob_start();
            include $__file;

            return (string) ob_get_clean();
        })($file, $data, $section);
    }
}
