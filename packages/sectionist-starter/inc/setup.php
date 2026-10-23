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
