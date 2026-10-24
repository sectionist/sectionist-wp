<?php

declare(strict_types=1);

namespace Sectionist\Sections;

/**
 * Shared "Backdrop" option of a section: none, a theme color or (optionally) an image.
 */
final class Backdrop
{
    /** @return list<Field> */
    public static function fields(bool $withImage = true): array
    {
        $options = [
            'none' => __('None', 'sectionist'),
            'background' => __('Color', 'sectionist'),
        ];
        if (! $withImage) {
            return [Field::choice('media', __('Backdrop', 'sectionist'), $options, 'none')];
        }

        return [
            Field::choice('media', __('Backdrop', 'sectionist'), $options + ['image' => __('Image', 'sectionist')], 'none'),
            Field::image('backdrop', __('Image', 'sectionist'))->showIf('media', 'image'),
        ];
    }
}
