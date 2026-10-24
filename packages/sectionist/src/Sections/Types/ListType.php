<?php

declare(strict_types=1);

namespace Sectionist\Sections\Types;

use Sectionist\Sections\AbstractSectionType;
use Sectionist\Sections\Backdrop;
use Sectionist\Sections\Field;

final class ListType extends AbstractSectionType
{
    public function name(): string
    {
        return 'list';
    }

    public function label(): string
    {
        return __('List', 'sectionist');
    }

    public function description(): string
    {
        return __('Rich text and a list of entries with title and text.', 'sectionist');
    }

    public function icon(): string
    {
        return 'list-view';
    }

    public function fields(): array
    {
        return [
            Field::richtext('body', __('Content', 'sectionist')),
            Field::list('entries', __('Entries', 'sectionist')),
            ...Backdrop::fields(false),
            Field::cta('cta', __('Call to action', 'sectionist')),
        ];
    }
}
