<?php

declare(strict_types=1);

namespace Sectionist\Sections;

use InvalidArgumentException;
use Sectionist\Storage\Section;

final class Registry
{
    public const HOOK = 'sectionist/sections/register';

    /** @var array<string, SectionType> */
    private array $types = [];

    private bool $loaded = false;

    public function register(SectionType $type): void
    {
        if (isset($this->types[$type->name()])) {
            throw new InvalidArgumentException(sprintf('Section type "%s" is already registered.', $type->name()));
        }

        $this->types[$type->name()] = $type;
    }

    public function get(string $name): ?SectionType
    {
        $this->load();

        return $this->types[$name] ?? null;
    }

    /**
     * @return array<string, SectionType>
     */
    public function all(): array
    {
        $this->load();

        return $this->types;
    }

    /**
     * Sanitizes sections with the sanitizer of their type. Sections of unknown
     * types are preserved unchanged; entries without id or type are dropped.
     * Container fields: `active` (missing means true) and `background` (a theme-defined key).
     *
     * @param  array<mixed>  $sections
     * @return list<Section>
     */
    public function sanitizeSections(array $sections): array
    {
        $clean = [];
        foreach ($sections as $raw) {
            if (! is_array($raw) || ! is_string($raw['id'] ?? null) || ! is_string($raw['type'] ?? null)) {
                continue;
            }
            if ($raw['id'] === '' || $raw['type'] === '') {
                continue;
            }

            $section = Section::fromArray($raw);
            $type = $this->get($section->type);
            if ($type === null) {
                $clean[] = $section;

                continue;
            }

            $clean[] = new Section(
                $section->id,
                $section->type,
                $section->active,
                sanitize_key($section->background),
                $type->sanitize($section->data),
            );
        }

        return $clean;
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;
        do_action(self::HOOK, $this);
    }
}
