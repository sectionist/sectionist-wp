<?php

declare(strict_types=1);

namespace Sectionist\Storage;

/**
 * Typed contract for one stored section. Unknown container fields are kept in `extra`.
 */
final readonly class Section
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public string $id,
        public string $type,
        public bool $active = true,
        public string $background = '',
        public array $data = [],
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>  $raw
     */
    public static function fromArray(array $raw): self
    {
        $known = ['id', 'type', 'active', 'background', 'data'];

        return new self(
            is_string($raw['id'] ?? null) ? $raw['id'] : '',
            is_string($raw['type'] ?? null) ? $raw['type'] : '',
            ($raw['active'] ?? true) !== false,
            is_string($raw['background'] ?? null) ? $raw['background'] : '',
            is_array($raw['data'] ?? null) ? $raw['data'] : [],
            array_diff_key($raw, array_flip($known)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->extra,
            'id' => $this->id,
            'type' => $this->type,
            'active' => $this->active,
            'background' => $this->background,
            'data' => $this->data,
        ];
    }
}
