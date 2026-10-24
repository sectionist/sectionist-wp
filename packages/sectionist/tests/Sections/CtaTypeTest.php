<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Field;
use Sectionist\Sections\Types\CtaType;

#[CoversClass(CtaType::class)]
#[CoversClass(Field::class)]
final class CtaTypeTest extends TestCase
{
    public function test_cta_is_always_active_and_required(): void
    {
        $type = new CtaType;
        $fields = array_column($type->fields(), null, 'name');

        $this->assertTrue($fields['cta']->required);
        $this->assertTrue($type->defaults()['cta']['active']);
        $this->assertTrue($type->sanitize(['cta' => ['active' => false, 'label' => 'Go']])['cta']['active']);
        $this->assertSame('Go', $type->sanitize(['cta' => ['label' => 'Go']])['cta']['label']);
    }

    public function test_has_backdrop_and_body(): void
    {
        $fields = array_column((new CtaType)->fields(), null, 'name');

        $this->assertSame('richtext', $fields['body']->kind);
        $this->assertSame(['field' => 'media', 'value' => 'image'], $fields['backdrop']->showIf);
        $this->assertSame('none', (new CtaType)->defaults()['media']);
    }
}
