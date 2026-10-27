<?php

declare(strict_types=1);

namespace Sectionist\Sections\Types;

use Sectionist\Sections\AbstractSectionType;
use Sectionist\Sections\Field;

final class EmbedType extends AbstractSectionType
{
    public function name(): string
    {
        return 'embed';
    }

    public function label(): string
    {
        return __('Embed', 'sectionist');
    }

    public function description(): string
    {
        return __('External media from an oEmbed provider, e.g. YouTube or Vimeo. The theme loads it only after consent.', 'sectionist');
    }

    public function icon(): string
    {
        return 'format-video';
    }

    public function fields(): array
    {
        return [
            Field::url('url', __('URL', 'sectionist')),
            Field::text('caption', __('Caption', 'sectionist')),
        ];
    }
}
