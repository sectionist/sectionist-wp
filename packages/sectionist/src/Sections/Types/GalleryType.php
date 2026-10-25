<?php

declare(strict_types=1);

namespace Sectionist\Sections\Types;

use Sectionist\Sections\AbstractSectionType;
use Sectionist\Sections\Backdrop;
use Sectionist\Sections\Field;

final class GalleryType extends AbstractSectionType
{
    public function name(): string
    {
        return 'gallery';
    }

    public function label(): string
    {
        return __('Gallery', 'sectionist');
    }

    public function description(): string
    {
        return __('Rich text and a list of images with optional captions.', 'sectionist');
    }

    public function icon(): string
    {
        return 'format-gallery';
    }

    public function fields(): array
    {
        return [
            Field::richtext('body', __('Content', 'sectionist')),
            Field::gallery('images', __('Images', 'sectionist')),
            ...Backdrop::fields(false),
            Field::cta('cta', __('Call to action', 'sectionist')),
        ];
    }
}
