<?php

declare(strict_types=1);

namespace Sectionist\Tests\Migration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Sectionist\Migration\Runner;
use Sectionist\Tests\Support\WpState;

#[CoversClass(Runner::class)]
final class RunnerTest extends TestCase
{
    protected function setUp(): void
    {
        WpState::reset();
    }

    public function test_fresh_install_stores_target_version(): void
    {
        $this->assertFalse((new Runner(1))->run());
        $this->assertSame(1, WpState::$options[Runner::OPTION]);
    }

    public function test_no_op_when_versions_match(): void
    {
        WpState::$options[Runner::OPTION] = 1;
        $calls = 0;

        $this->assertFalse((new Runner(1, [0 => function () use (&$calls): void {
            $calls++;
        }]))->run());
        $this->assertSame(0, $calls);
    }

    public function test_runs_steps_in_order_and_raises_version(): void
    {
        WpState::$options[Runner::OPTION] = 1;
        $log = [];

        $runner = new Runner(3, [
            2 => function () use (&$log): void {
                $log[] = 2;
            },
            1 => function () use (&$log): void {
                $log[] = 1;
            },
        ]);

        $this->assertTrue($runner->run());
        $this->assertSame([1, 2], $log);
        $this->assertSame(3, WpState::$options[Runner::OPTION]);
        $this->assertFalse(get_transient(Runner::LOCK));
    }

    public function test_lock_prevents_double_run(): void
    {
        WpState::$options[Runner::OPTION] = 1;
        set_transient(Runner::LOCK, 1, 60);
        $calls = 0;

        $this->assertFalse((new Runner(2, [1 => function () use (&$calls): void {
            $calls++;
        }]))->run());
        $this->assertSame(0, $calls);
    }

    public function test_missing_step_keeps_version_and_releases_lock(): void
    {
        WpState::$options[Runner::OPTION] = 1;

        $this->assertFalse((new Runner(2))->run());
        $this->assertSame(1, WpState::$options[Runner::OPTION]);
        $this->assertFalse(get_transient(Runner::LOCK));
    }
}
