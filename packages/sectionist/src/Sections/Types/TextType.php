<?php

declare(strict_types=1);

namespace Sectionist\Sections\Types;

use Sectionist\Sections\Field;
use Sectionist\Sections\SectionType;

final class TextType implements SectionType
{
    public function name(): string
    {
        return 'text';
    }

    public function label(): string
    {
        return __('Text', 'sectionist');
    }

    public function description(): string
    {
        return __('Rich text with headings, lists and links.', 'sectionist');
    }

    public function icon(): string
    {
        return 'editor-paragraph';
    }

    public function fields(): array
    {
        return [
            Field::richtext('body', __('Content', 'sectionist')),
        ];
    }

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
