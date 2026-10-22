<?php

declare(strict_types=1);

namespace Sectionist\Tests\Storage;

use PHPUnit\Framework\TestCase;
use Sectionist\Storage\PostMetaRepository;
use Sectionist\Storage\Revisions;

final class RevisionsTest extends TestCase
{
    public function test_adds_sections_meta_key_and_keeps_existing_keys(): void
    {
        $keys = new Revisions()->keys(['other_key']);

        $this->assertSame(['other_key', PostMetaRepository::META_KEY], $keys);
    }
}
