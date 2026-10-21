<?php

declare(strict_types=1);

namespace Sectionist\Sections;

interface SectionType
{
    public function name(): string;

    public function label(): string;

    /**
     * @return list<Field>
     */
    public function fields(): array;

    /**
     * @return array<string, string>
     */
    public function defaults(): array;

    /**
     * @param  array<mixed>  $data
     * @return array<string, string>
     */
    public function sanitize(array $data): array;
}
