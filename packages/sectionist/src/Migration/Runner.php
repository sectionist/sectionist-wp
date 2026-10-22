<?php

declare(strict_types=1);

namespace Sectionist\Migration;

final class Runner
{
    public const OPTION = 'sectionist_db_version';

    public const LOCK = 'sectionist_migration_lock';

    /**
     * @param  array<int, callable(): void>  $steps  keyed by the version they migrate from
     */
    public function __construct(
        private readonly int $targetVersion,
        private readonly array $steps = [],
    ) {}

    public function register(): void
    {
        add_action('admin_init', $this->run(...));
    }

    /**
     * Returns true when at least one step ran and the version was raised.
     */
    public function run(): bool
    {
        $current = get_option(self::OPTION, false);
        if ($current === false) {
            update_option(self::OPTION, $this->targetVersion);

            return false;
        }

        $current = is_numeric($current) ? (int) $current : 0;
        if ($current >= $this->targetVersion || get_transient(self::LOCK) !== false) {
            return false;
        }

        set_transient(self::LOCK, 1, 60);
        try {
            for ($version = $current; $version < $this->targetVersion; $version++) {
                if (! isset($this->steps[$version])) {
                    return false;
                }
                ($this->steps[$version])();
                update_option(self::OPTION, $version + 1);
            }
        } finally {
            delete_transient(self::LOCK);
        }

        return true;
    }
}
