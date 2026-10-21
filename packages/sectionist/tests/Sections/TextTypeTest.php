<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Field;
use Sectionist\Sections\Types\TextType;

#[CoversClass(TextType::class)]
#[CoversClass(Field::class)]
final class TextTypeTest extends TestCase
{
    public function testStripsDisallowedHtmlAndInlineStyles(): void
    {
        $clean = (new TextType())->sanitize([
            'heading' => '<b>Hi</b>',
            'body' => '<p style="color:red">A <strong>b</strong> <script>x</script><a href="/x" onclick="y">l</a></p>',
        ]);

        $this->assertSame('Hi', $clean['heading']);
        $this->assertSame('<p>A <strong>b</strong> x<a href="/x">l</a></p>', $clean['body']);
    }

    public function testKeepsHeadingsDropsBrAndRestrictsParagraphClasses(): void
    {
        $clean = (new TextType())->sanitize([
            'body' => '<h2>T</h2>a<br><p class="as-h2 evil">x</p><p class="evil">y</p>',
        ]);

        $this->assertSame('<h2>T</h2>a<p class="as-h2">x</p><p>y</p>', $clean['body']);
    }

    public function testMissingOrInvalidFieldsFallBackToDefaults(): void
    {
        $clean = (new TextType())->sanitize(['heading' => ['nope']]);

        $this->assertSame(['heading' => '', 'body' => ''], $clean);
    }

    public function testDefaultsMatchFields(): void
    {
        $this->assertSame(['heading' => '', 'body' => ''], (new TextType())->defaults());
    }

    public function testSelectRejectsValuesOutsideOptions(): void
    {
        $field = Field::select('variant', 'Variant', ['light', 'dark'], 'light');

        $this->assertSame('dark', $field->sanitize('dark'));
        $this->assertSame('light', $field->sanitize('neon'));
    }
}
