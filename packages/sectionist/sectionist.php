<?php

/**
 * Plugin Name: Sectionist
 * Description: Structured section editor for WordPress.
 * Version: 0.4.0
 * Requires at least: 7.0
 * Requires PHP: 8.3
 * License: GPL-3.0-or-later
 * Text Domain: sectionist
 */

declare(strict_types=1);

use Sectionist\Plugin;

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__.'/vendor/autoload.php';

require_once __DIR__.'/src/functions.php';

Plugin::boot();
