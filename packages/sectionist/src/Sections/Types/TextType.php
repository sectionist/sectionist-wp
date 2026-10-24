<?php

declare(strict_types=1);

namespace Sectionist\Sections\Types;

use Sectionist\Sections\AbstractSectionType;
use Sectionist\Sections\Backdrop;
use Sectionist\Sections\Field;

final class TextType extends AbstractSectionType
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
            ...Backdrop::fields(false),
            Field::media('image', __('Image', 'sectionist')),
            Field::cta('cta', __('Call to action', 'sectionist')),
        ];
    }
}
