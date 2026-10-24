<?php

declare(strict_types=1);

namespace Sectionist\Sections\Types;

use Sectionist\Sections\AbstractSectionType;
use Sectionist\Sections\Backdrop;
use Sectionist\Sections\Field;

final class CtaType extends AbstractSectionType
{
    public function name(): string
    {
        return 'cta';
    }

    public function label(): string
    {
        return __('Call to action', 'sectionist');
    }

    public function description(): string
    {
        return __('Closing section with text and a button or link. Always placed at the end.', 'sectionist');
    }

    public function icon(): string
    {
        return 'megaphone';
    }

    public function fields(): array
    {
        return [
            Field::richtext('body', __('Content', 'sectionist')),
            Field::cta('cta', __('Link', 'sectionist'), true),
            ...Backdrop::fields(),
        ];
    }
}
