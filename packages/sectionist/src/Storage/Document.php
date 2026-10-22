<?php

declare(strict_types=1);

namespace Sectionist\Storage;

final readonly class Document
{
    public const CURRENT_VERSION = 1;

    /**
     * @param  list<Section>  $sections
     */
    public function __construct(
        public array $sections = [],
        public int $schemaVersion = self::CURRENT_VERSION,
    ) {}

    public static function fromJson(string $json): self
    {
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return new self;
        }

        $version = $decoded['schema_version'] ?? self::CURRENT_VERSION;
        $sections = is_array($decoded['sections'] ?? null) ? array_values($decoded['sections']) : [];

        return new self(
            array_values(array_map(
                static fn (array $section): Section => Section::fromArray($section),
                array_filter($sections, 'is_array'),
            )),
            is_int($version) ? $version : self::CURRENT_VERSION,
        );
    }

    public function isNewerThanSupported(): bool
    {
        return $this->schemaVersion > self::CURRENT_VERSION;
    }

    /**
     * Sections that are visible on the public site (`active` missing means true).
     *
     * @return list<Section>
     */
    public function activeSections(): array
    {
        return array_values(array_filter(
            $this->sections,
            static fn (Section $section): bool => $section->active,
        ));
    }

    /**
     * @return array{schema_version: int, sections: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'schema_version' => $this->schemaVersion,
            'sections' => array_map(static fn (Section $section): array => $section->toArray(), $this->sections),
        ];
    }
}
