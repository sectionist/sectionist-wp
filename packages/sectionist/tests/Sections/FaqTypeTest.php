<?php

declare(strict_types=1);

namespace Sectionist\Tests\Sections;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Faq\Faq;
use Sectionist\Sections\Types\FaqType;
use Sectionist\Tests\Support\WpState;

#[CoversClass(FaqType::class)]
#[CoversClass(Faq::class)]
final class FaqTypeTest extends TestCase
{
    protected function setUp(): void
    {
        WpState::reset();
        WpState::$terms = [3, 4];
        WpState::$postTypes = [10 => Faq::POST_TYPE, 11 => Faq::POST_TYPE, 12 => 'page'];
    }

    public function test_defaults_select_by_topics_without_topics(): void
    {
        $this->assertSame(['mode' => 'topics', 'topics' => [], 'entries' => [], 'semantic' => true], (new FaqType)->defaults()['faq']);
    }

    public function test_keeps_valid_topics_and_entries_in_order(): void
    {
        $clean = (new FaqType)->sanitize(['faq' => ['mode' => 'selected', 'topics' => ['4', 3, 99], 'entries' => [11, 10, 10]]])['faq'];

        $this->assertSame('selected', $clean['mode']);
        $this->assertSame([4, 3], $clean['topics']);
        $this->assertSame([11, 10], $clean['entries']);
    }

    public function test_drops_entries_of_other_post_types_and_unknown_ids(): void
    {
        $clean = (new FaqType)->sanitize(['faq' => ['entries' => [12, 404, 10]]])['faq'];

        $this->assertSame([10], $clean['entries']);
    }

    public function test_invalid_mode_and_value_fall_back(): void
    {
        $this->assertSame('topics', (new FaqType)->sanitize(['faq' => ['mode' => 'evil']])['faq']['mode']);
        $this->assertSame(Faq::defaults(), (new FaqType)->sanitize(['faq' => 'x'])['faq']);
    }

    public function test_question_level_follows_section_heading(): void
    {
        $this->assertSame(3, Faq::questionLevel('<h2>Title</h2><p>x</p>', ['semantic' => true]));
        $this->assertSame(6, Faq::questionLevel('<h6>Title</h6>', []));
        $this->assertSame(2, Faq::questionLevel('<p>No heading</p>', []));
        $this->assertSame(0, Faq::questionLevel('<h2>Title</h2>', ['semantic' => false]));
    }
}
