<?php

declare(strict_types=1);

namespace Sectionist\Tests\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Admin\MetaBox;
use Sectionist\Admin\Settings;
use Sectionist\Sections\Registry;
use Sectionist\Sections\Types\CtaType;
use Sectionist\Sections\Types\TextType;
use Sectionist\Tests\Support\WpState;

#[CoversClass(Settings::class)]
final class SettingsTest extends TestCase
{
    private Settings $settings;

    protected function setUp(): void
    {
        WpState::reset();
        $registry = new Registry;
        $registry->register(new TextType);
        $registry->register(new CtaType);
        $this->settings = new Settings($registry);
    }

    public function test_everything_is_allowed_by_default(): void
    {
        $this->assertTrue(Settings::isAllowed('page', 'text'));
        $this->assertTrue(Settings::isAllowed('post', 'cta'));
    }

    public function test_unchecked_cells_are_denied_and_checked_cells_allowed(): void
    {
        $postType = MetaBox::postTypes()[0];
        $clean = $this->settings->sanitize(['allowed' => [$postType => ['text' => '1']]]);
        WpState::$options[Settings::OPTION] = $clean;

        $this->assertTrue(Settings::isAllowed($postType, 'text'));
        $this->assertFalse(Settings::isAllowed($postType, 'cta'));
    }

    public function test_cells_can_be_allowed_again(): void
    {
        $postType = MetaBox::postTypes()[0];
        WpState::$options[Settings::OPTION] = ['disabled' => [$postType => ['text']]];

        $all = ['text' => '1', 'cta' => '1'];
        $clean = $this->settings->sanitize(['allowed' => array_fill_keys(MetaBox::postTypes(), $all)]);

        $this->assertSame([], $clean['disabled']);
    }

    public function test_unknown_stored_types_are_preserved(): void
    {
        $postType = MetaBox::postTypes()[0];
        WpState::$options[Settings::OPTION] = ['disabled' => [$postType => ['legacy']]];

        $all = ['text' => '1', 'cta' => '1'];
        $clean = $this->settings->sanitize(['allowed' => array_fill_keys(MetaBox::postTypes(), $all)]);

        $this->assertSame(['legacy'], $clean['disabled'][$postType]);
    }
}
