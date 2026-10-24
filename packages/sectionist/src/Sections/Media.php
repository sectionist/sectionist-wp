<?php

declare(strict_types=1);

namespace Sectionist\Sections;

/**
 * Reusable optional image value: a switch, a media library attachment and a position.
 * Any section type can use it through `Field::media()`. Without an active switch or a valid
 * attachment, nothing is rendered.
 */
final class Media
{
    public const POSITIONS = ['left', 'right', 'top', 'bottom'];

    /**
     * @return array{active: bool, id: int, position: string}
     */
    public static function defaults(): array
    {
        return ['active' => false, 'id' => 0, 'position' => 'right'];
    }

    /**
     * @return array{active: bool, id: int, position: string}
     */
    public static function sanitize(mixed $value): array
    {
        $in = is_array($value) ? $value : [];
        $defaults = self::defaults();
        $id = is_int($in['id'] ?? null) || is_string($in['id'] ?? null) ? absint($in['id']) : 0;
        $position = is_string($in['position'] ?? null) ? $in['position'] : '';

        return [
            'active' => ($in['active'] ?? false) === true,
            'id' => $id > 0 && wp_attachment_is_image($id) ? $id : 0,
            'position' => in_array($position, self::POSITIONS, true) ? $position : $defaults['position'],
        ];
    }

    /**
     * Attachment ID of a sanitized media value to render, or 0 when it is inactive or has no image.
     *
     * @param  array<string, mixed>  $media
     */
    public static function id(array $media): int
    {
        return ($media['active'] ?? false) === true && is_int($media['id'] ?? null) ? $media['id'] : 0;
    }
}
