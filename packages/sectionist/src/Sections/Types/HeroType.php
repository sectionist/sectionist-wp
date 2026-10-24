<?php

declare(strict_types=1);

namespace Sectionist\Sections\Types;

use Sectionist\Sections\AbstractSectionType;
use Sectionist\Sections\Backdrop;
use Sectionist\Sections\Field;

final class HeroType extends AbstractSectionType
{
    public function name(): string
    {
        return 'hero';
    }

    public function label(): string
    {
        return __('Hero', 'sectionist');
    }

    public function description(): string
    {
        return __('Large intro with image, headline, text and call to action.', 'sectionist');
    }

    public function icon(): string
    {
        return 'cover-image';
    }

    public function fields(): array
    {
        return [
            Field::richtext('body', __('Content', 'sectionist')),
            ...Backdrop::fields(),
            Field::cta('cta', __('Call to action', 'sectionist')),
        ];
    }
}
