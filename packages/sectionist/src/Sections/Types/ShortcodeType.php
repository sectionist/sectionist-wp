<?php

declare(strict_types=1);

namespace Sectionist\Sections\Types;

use Sectionist\Sections\AbstractSectionType;
use Sectionist\Sections\Backdrop;
use Sectionist\Sections\Field;

final class ShortcodeType extends AbstractSectionType
{
    public function name(): string
    {
        return 'shortcode';
    }

    public function label(): string
    {
        return __('Shortcode', 'sectionist');
    }

    public function description(): string
    {
        return __('Rich text and a list of shortcodes from other plugins, e.g. forms.', 'sectionist');
    }

    public function icon(): string
    {
        return 'shortcode';
    }

    public function fields(): array
    {
        return [
            Field::richtext('body', __('Content', 'sectionist')),
            Field::shortcodes('shortcodes', __('Shortcodes', 'sectionist')),
            ...Backdrop::fields(false),
            Field::cta('cta', __('Call to action', 'sectionist')),
        ];
    }
}
