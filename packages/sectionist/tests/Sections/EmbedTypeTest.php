<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Field;
use Sectionist\Sections\Types\EmbedType;

#[CoversClass(EmbedType::class)]
#[CoversClass(Field::class)]
final class EmbedTypeTest extends TestCase
{
    public function test_defaults_are_empty_url_and_caption(): void
    {
        $this->assertSame(['url' => '', 'caption' => ''], (new EmbedType)->defaults());
    }

    public function test_url_field_uses_the_url_kind(): void
    {
        $fields = array_column((new EmbedType)->fields(), null, 'name');

        $this->assertSame('url', $fields['url']->kind);
        $this->assertSame('text', $fields['caption']->kind);
    }

    public function test_keeps_https_url_and_trims_whitespace(): void
    {
        $clean = (new EmbedType)->sanitize(['url' => '  https://www.youtube.com/watch?v=x  ']);

        $this->assertSame('https://www.youtube.com/watch?v=x', $clean['url']);
    }

    public function test_adds_https_to_scheme_less_input(): void
    {
        $clean = (new EmbedType)->sanitize(['url' => 'www.youtube.com/watch?v=x']);

        $this->assertSame('https://www.youtube.com/watch?v=x', $clean['url']);
    }

    public function test_rejects_unsupported_urls(): void
    {
        $type = new EmbedType;

        foreach (['http://example.test', 'ftp://example.test', 'javascript:alert(1)', 'mailto:a@example.test', '/relative', '//example.test/x', ''] as $url) {
            $this->assertSame('', $type->sanitize(['url' => $url])['url'], $url);
        }

        $this->assertSame('', $type->sanitize(['url' => ['https://example.test']])['url']);
    }

    public function test_keeps_query_string_ampersands(): void
    {
        $clean = (new EmbedType)->sanitize(['url' => 'https://example.test/watch?v=x&t=1']);

        $this->assertSame('https://example.test/watch?v=x&t=1', $clean['url']);
    }

    public function test_rejects_https_url_without_host(): void
    {
        $this->assertSame('', (new EmbedType)->sanitize(['url' => 'https://'])['url']);
    }

    public function test_sanitizes_caption_and_drops_unknown_fields(): void
    {
        $clean = (new EmbedType)->sanitize([
            'url' => 'https://example.test/video',
            'caption' => '<b> A   caption </b>',
            'provider' => 'youtube',
        ]);

        $this->assertSame(['url' => 'https://example.test/video', 'caption' => 'A caption'], $clean);
    }
}
