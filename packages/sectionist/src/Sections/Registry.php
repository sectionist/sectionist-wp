<?php

declare(strict_types=1);

namespace Sectionist\Sections;

use InvalidArgumentException;

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
     *
     * @param  array<mixed>  $sections
     * @return list<array<string, mixed>>
     */
    public function sanitizeSections(array $sections): array
    {
        $clean = [];
        foreach ($sections as $section) {
            if (! is_array($section) || ! is_string($section['id'] ?? null) || ! is_string($section['type'] ?? null)) {
                continue;
            }
            if ($section['id'] === '' || $section['type'] === '') {
                continue;
            }

            $type = $this->get($section['type']);
            if ($type === null) {
                $clean[] = $section;

                continue;
            }

            $data = is_array($section['data'] ?? null) ? $section['data'] : [];
            $clean[] = [
                'id' => $section['id'],
                'type' => $section['type'],
                'data' => $type->sanitize($data),
            ];
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
