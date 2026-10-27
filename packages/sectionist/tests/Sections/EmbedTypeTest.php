<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Cta;
use Sectionist\Sections\Field;
use Sectionist\Sections\Types\EmbedType;

#[CoversClass(EmbedType::class)]
#[CoversClass(Field::class)]
final class EmbedTypeTest extends TestCase
{
    public function test_defaults_match_fields(): void
    {
        $this->assertSame([
            'body' => '',
            'url' => '',
            'caption' => '',
            'media' => 'none',
            'cta' => Cta::defaults(),
        ], (new EmbedType)->defaults());
    }

    public function test_url_and_caption_field_kinds(): void
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

        $this->assertSame('https://example.test/video', $clean['url']);
        $this->assertSame('A caption', $clean['caption']);
        $this->assertArrayNotHasKey('provider', $clean);
    }

    public function test_sanitizes_body_through_the_rich_text_allowlist(): void
    {
        // wp_kses() drops the <script> tag but keeps its text node (wp_kses_split2()),
        // so this proves the field is wired to the rich-text allowlist, not the sanitizer itself.
        $clean = (new EmbedType)->sanitize([
            'body' => '<p class="as-h2 has-text-align-center">x</p><script>alert(1)</script>',
        ]);

        $this->assertSame('<p class="as-h2 has-text-align-center">x</p>alert(1)', $clean['body']);
    }

    public function test_media_only_accepts_known_choices(): void
    {
        $type = new EmbedType;

        $this->assertSame('none', $type->defaults()['media']);
        $this->assertSame('background', $type->sanitize(['media' => 'background'])['media']);
        $this->assertSame('none', $type->sanitize(['media' => 'image'])['media']);
        $this->assertSame('none', $type->sanitize(['media' => 'video'])['media']);
    }

    public function test_backdrop_is_color_only(): void
    {
        $type = new EmbedType;
        $fields = array_column($type->fields(), null, 'name');

        $this->assertArrayNotHasKey('backdrop', $fields);
        $this->assertSame('choice', $fields['media']->kind);
        $this->assertArrayNotHasKey('backdrop', $type->defaults());
        $this->assertArrayNotHasKey('backdrop', $type->sanitize(['backdrop' => 5]));
    }

    public function test_sanitizes_cta(): void
    {
        $type = new EmbedType;

        $inactive = $type->sanitize(['cta' => [
            'active' => 'yes',
            'label' => '<b> Go </b>  now',
            'url' => 'https://example.test/go',
        ]])['cta'];

        $this->assertFalse($inactive['active']);
        $this->assertSame('Go now', $inactive['label']);

        $active = $type->sanitize(['cta' => ['active' => true, 'label' => 'Go']])['cta'];

        $this->assertTrue($active['active']);
    }
}
