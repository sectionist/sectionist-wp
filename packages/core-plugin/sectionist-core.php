<?php
/**
 * Plugin Name: Sectionist Core
 * Description: Structured section editor core for WordPress.
 * Version: 0.0.1
 * Requires PHP: 8.5
 * License: GPL-3.0-or-later
 * Text Domain: sectionist-core
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

\Sectionist\Core\Plugin::boot();