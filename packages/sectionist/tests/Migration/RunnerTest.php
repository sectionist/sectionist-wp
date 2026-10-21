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

    public function testFreshInstallStoresTargetVersion(): void
    {
        $this->assertFalse((new Runner(1))->run());
        $this->assertSame(1, WpState::$options[Runner::OPTION]);
    }

    public function testNoOpWhenVersionsMatch(): void
    {
        WpState::$options[Runner::OPTION] = 1;
        $calls = 0;

        $this->assertFalse((new Runner(1, [0 => function () use (&$calls): void {
            $calls++;
        }]))->run());
        $this->assertSame(0, $calls);
    }

    public function testRunsStepsInOrderAndRaisesVersion(): void
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

    public function testLockPreventsDoubleRun(): void
    {
        WpState::$options[Runner::OPTION] = 1;
        set_transient(Runner::LOCK, 1, 60);
        $calls = 0;

        $this->assertFalse((new Runner(2, [1 => function () use (&$calls): void {
            $calls++;
        }]))->run());
        $this->assertSame(0, $calls);
    }

    public function testMissingStepKeepsVersionAndReleasesLock(): void
    {
        WpState::$options[Runner::OPTION] = 1;

        $this->assertFalse((new Runner(2))->run());
        $this->assertSame(1, WpState::$options[Runner::OPTION]);
        $this->assertFalse(get_transient(Runner::LOCK));
    }
}
