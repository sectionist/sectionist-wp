<?php

/**
 * Plugin Name: Sectionist
 * Description: Structured section editor for WordPress.
 * Version: 0.0.1
 * Requires at least Wordpress: 6.4
 * Requires PHP: 8.5
 * License: GPL-3.0-or-later
 * Text Domain: sectionist
 */

declare(strict_types=1);

use Sectionist\Plugin;

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

Plugin::boot();
