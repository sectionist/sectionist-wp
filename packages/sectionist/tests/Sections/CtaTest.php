<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\TestCase;
use Sectionist\Sections\Cta;

final class CtaTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['sectionist_test_posts'] = [
            5 => ['status' => 'publish', 'url' => 'https://example.test/about/'],
            6 => ['status' => 'draft', 'url' => 'https://example.test/?p=6'],
        ];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['sectionist_test_posts']);
    }

    public function test_invalid_input_gives_inactive_defaults(): void
    {
        $this->assertSame(Cta::defaults(), Cta::sanitize('nope'));
    }

    public function test_renders_requires_active_target_and_label(): void
    {
        $this->assertTrue(Cta::renders(['active' => true, 'label' => 'Go', 'url' => 'https://x.test']));
        $this->assertTrue(Cta::renders(['active' => true, 'label' => 'Go', 'page_id' => 5]));

        $this->assertFalse(Cta::renders(['active' => true, 'label' => '', 'url' => 'https://x.test']));
        $this->assertFalse(Cta::renders(['active' => true, 'label' => ['Go'], 'url' => 'https://x.test']));
        $this->assertFalse(Cta::renders(['active' => true, 'label' => 'Go']));
        $this->assertFalse(Cta::renders(['active' => true, 'label' => 'Go', 'page_id' => 6]));
        $this->assertFalse(Cta::renders(['active' => false, 'label' => 'Go', 'url' => 'https://x.test']));
        $this->assertFalse(Cta::renders('nope'));
    }

    public function test_fields_are_sanitized(): void
    {
        $cta = Cta::sanitize([
            'active' => true,
            'style' => 'weird',
            'new_tab' => true,
            'page_id' => '5',
            'url' => 'javascript:alert(1)',
        ]);

        $this->assertTrue($cta['active']);
        $this->assertSame('button', $cta['style']);
        $this->assertTrue($cta['new_tab']);
        $this->assertSame(5, $cta['page_id']);
        $this->assertSame('', $cta['url']);
    }

    public function test_url_resolves_published_page_only(): void
    {
        $this->assertSame('https://example.test/about/', Cta::url(['page_id' => 5]));
        $this->assertSame('https://x.test', Cta::url(['page_id' => 5, 'url' => 'https://x.test']));
        $this->assertSame('', Cta::url(['page_id' => 6]));
        $this->assertSame('', Cta::url(['page_id' => 99]));
        $this->assertSame('https://x.test', Cta::url(['url' => 'https://x.test']));
    }
}
