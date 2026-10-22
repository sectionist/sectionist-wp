<?php

declare(strict_types=1);

namespace Sectionist\Sections;

/**
 * Base for section types that are fully described by their field schema:
 * defaults and sanitizing are derived from `fields()`, so a new type only declares its fields.
 */
abstract class AbstractSectionType implements SectionType
{
    public function defaults(): array
    {
        $defaults = [];
        foreach ($this->fields() as $field) {
            $defaults[$field->name] = $field->default;
        }

        return $defaults;
    }

    public function sanitize(array $data): array
    {
        $clean = [];
        foreach ($this->fields() as $field) {
            $clean[$field->name] = $field->sanitize($data[$field->name] ?? null);
        }

        return $clean;
    }
}
