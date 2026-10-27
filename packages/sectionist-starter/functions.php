<?php

declare(strict_types=1);

add_action('after_setup_theme', static function (): void {
    load_theme_textdomain('sectionist-starter', get_template_directory().'/languages');

    add_theme_support('title-tag');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('Primary menu', 'sectionist-starter'),
    ]);
});

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_style(
        'sectionist-starter',
        get_theme_file_uri('resources/css/theme.css'),
        [],
        wp_get_theme()->get('Version'),
    );

    wp_enqueue_script(
        'sectionist-starter-embed',
        get_theme_file_uri('resources/js/embed.js'),
        [],
        wp_get_theme()->get('Version'),
        true,
    );
});

/**
 * Load the template hierarchy from `templates/` instead of the theme root.
 * WordPress still decides which template wins; only the lookup folder changes.
 */
foreach ([
    '404', 'archive', 'attachment', 'author', 'category', 'date', 'embed', 'frontpage',
    'home', 'index', 'page', 'paged', 'privacypolicy', 'search', 'single', 'singular',
    'tag', 'taxonomy',
] as $type) {
    add_filter(
        "{$type}_template_hierarchy",
        static fn (array $templates): array => array_map(
            static fn (string $template): string => 'templates/'.$template,
            $templates,
        ),
    );
}
