<?php

declare(strict_types=1);

namespace Sectionist\Sections;

interface SectionType
{
    public function name(): string;

    public function label(): string;

    public function description(): string;

    /**
     * Dashicons name without the `dashicons-` prefix.
     */
    public function icon(): string;

    /**
     * @return list<Field>
     */
    public function fields(): array;

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array;

    /**
     * @param  array<mixed>  $data
     * @return array<string, mixed>
     */
    public function sanitize(array $data): array;
}
