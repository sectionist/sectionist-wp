<?php

declare(strict_types=1);

namespace Sectionist\Storage;

final readonly class Document
{
    public const CURRENT_VERSION = 1;

    /**
     * @param list<array<string, mixed>> $sections
     */
    public function __construct(
        public array $sections = [],
        public int $schemaVersion = self::CURRENT_VERSION,
    ) {
    }

    public static function fromJson(string $json): self
    {
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return new self();
        }

        $version = $decoded['schema_version'] ?? self::CURRENT_VERSION;
        $sections = is_array($decoded['sections'] ?? null) ? array_values($decoded['sections']) : [];

        return new self(
            array_values(array_filter($sections, 'is_array')),
            is_int($version) ? $version : self::CURRENT_VERSION,
        );
    }

    public function isNewerThanSupported(): bool
    {
        return $this->schemaVersion > self::CURRENT_VERSION;
    }

    /**
     * @return array{schema_version: int, sections: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return ['schema_version' => $this->schemaVersion, 'sections' => $this->sections];
    }
}
