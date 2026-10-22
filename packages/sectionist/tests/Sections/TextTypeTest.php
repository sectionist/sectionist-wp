<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Cta;
use Sectionist\Sections\Field;
use Sectionist\Sections\Types\TextType;

#[CoversClass(TextType::class)]
#[CoversClass(Field::class)]
final class TextTypeTest extends TestCase
{
    public function test_strips_disallowed_html_and_inline_styles(): void
    {
        $clean = (new TextType)->sanitize([
            'body' => '<p style="color:red">A <strong>b</strong> <script>x</script><a href="/x" onclick="y">l</a></p>',
        ]);

        $this->assertSame('<p>A <strong>b</strong> x<a href="/x">l</a></p>', $clean['body']);
    }

    public function test_keeps_headings_drops_br_and_restricts_paragraph_classes(): void
    {
        $clean = (new TextType)->sanitize([
            'body' => '<h2>T</h2>a<br><p class="as-h2 evil">x</p><p class="evil">y</p>',
        ]);

        $this->assertSame('<h2>T</h2>a<p class="as-h2">x</p><p>y</p>', $clean['body']);
    }

    public function test_keeps_alignment_classes_and_strike_only(): void
    {
        $clean = (new TextType)->sanitize([
            'body' => '<h3 class="has-text-align-center as-h1">T</h3><p class="has-text-align-right as-h2"><s>x</s></p>',
        ]);

        $this->assertSame('<h3 class="has-text-align-center">T</h3><p class="has-text-align-right as-h2"><s>x</s></p>', $clean['body']);
    }

    public function test_keeps_only_https_mailto_and_tel_links(): void
    {
        $clean = (new TextType)->sanitize([
            'body' => '<p><a href="https://a.de">1</a><a href="mailto:a@b.de">2</a><a href="tel:+4930123">3</a>'
                .'<a href="http://a.de">4</a><a href="javascript:alert(1)">5</a><a href="ftp://a.de">6</a></p>',
        ]);

        $this->assertSame(
            '<p><a href="https://a.de">1</a><a href="mailto:a@b.de">2</a><a href="tel:+4930123">3</a><a>4</a><a>5</a><a>6</a></p>',
            $clean['body'],
        );
    }

    public function test_missing_or_invalid_fields_fall_back_to_defaults(): void
    {
        $clean = (new TextType)->sanitize(['body' => ['nope']]);

        $this->assertSame(['body' => '', 'cta' => Cta::defaults()], $clean);
    }

    public function test_defaults_match_fields(): void
    {
        $this->assertSame(['body' => '', 'cta' => Cta::defaults()], (new TextType)->defaults());
    }

    public function test_select_rejects_values_outside_options(): void
    {
        $field = Field::select('variant', 'Variant', ['light', 'dark'], 'light');

        $this->assertSame('dark', $field->sanitize('dark'));
        $this->assertSame('light', $field->sanitize('neon'));
    }
}
