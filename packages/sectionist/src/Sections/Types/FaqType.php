<?php

declare(strict_types=1);

namespace Sectionist\Sections\Types;

use Sectionist\Sections\AbstractSectionType;
use Sectionist\Sections\Backdrop;
use Sectionist\Sections\Field;

final class FaqType extends AbstractSectionType
{
    public function name(): string
    {
        return 'faq';
    }

    public function label(): string
    {
        return __('FAQ', 'sectionist');
    }

    public function description(): string
    {
        return __('Questions and answers from the central FAQ, by topic or hand-picked.', 'sectionist');
    }

    public function icon(): string
    {
        return 'editor-help';
    }

    public function fields(): array
    {
        return [
            Field::richtext('body', __('Content', 'sectionist')),
            Field::faq('faq', __('Questions', 'sectionist')),
            ...Backdrop::fields(false),
            Field::cta('cta', __('Call to action', 'sectionist')),
        ];
    }
}
