<?php

declare(strict_types=1);

use Sectionist\Storage\Section;

/**
 * Theme view for the "shortcode" section type. Overrides the plugin fallback.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
$items = is_array($data['shortcodes'] ?? null) ? $data['shortcodes'] : [];
$background = ($data['media'] ?? 'none') === 'background' ? (string) $section->background : '';
?>
<section class="section section--shortcode<?php echo $background !== '' ? ' section--bg-'.esc_attr($background) : ''; ?>">
<div class="section__content">
<?php echo wp_kses_post($body); ?>
<?php foreach ($items as $item) { ?>
<?php if (! is_array($item) || ! is_string($item['code'] ?? null)) {
    continue;
} ?>
<div class="section__shortcode"><?php echo do_shortcode($item['code']); ?></div>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</div>
</section>
