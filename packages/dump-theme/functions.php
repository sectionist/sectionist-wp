<?php

declare(strict_types=1);

/**
 * Example backgrounds so the editor's swatch picker can be tested.
 * Only the key is stored; this theme does not style them.
 */
add_filter('sectionist/backgrounds', static fn (array $backgrounds): array => [
    ...$backgrounds,
    ['key' => 'white', 'label' => __('White', 'sectionist-dump-theme'), 'color' => '#ffffff'],
    ['key' => 'light', 'label' => __('Light', 'sectionist-dump-theme'), 'color' => '#f4f4f4'],
    ['key' => 'dark', 'label' => __('Dark', 'sectionist-dump-theme'), 'color' => '#1e1e1e'],
    ['key' => 'brand', 'label' => __('Brand', 'sectionist-dump-theme'), 'color' => '#2563eb'],
]);
