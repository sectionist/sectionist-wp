<?php

declare(strict_types=1);

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_style(
        'sectionist-starter',
        get_theme_file_uri('assets/css/main.css'),
        [],
        wp_get_theme()->get('Version'),
    );
});
