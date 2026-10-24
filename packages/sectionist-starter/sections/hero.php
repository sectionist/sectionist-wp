<?php

declare(strict_types=1);

use Sectionist\Storage\Section;

/**
 * Theme view for the "hero" section type. Overrides the plugin fallback.
 * The headline is part of `body` (the editor picks the heading level in the rich text).
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$image = ($data['media'] ?? 'none') === 'image' && is_int($data['backdrop'] ?? null) ? $data['backdrop'] : 0;
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
$background = ($data['media'] ?? 'none') === 'background' ? (string) $section->background : '';
?>
<section class="section section--hero<?php echo $background !== '' ? ' section--bg-'.esc_attr($background) : ''; ?>">
<?php echo $image > 0 ? wp_get_attachment_image($image, 'full', false, ['class' => 'section__backdrop']) : ''; ?>
<div class="section__content">
<?php echo wp_kses_post($body); ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</div>
</section>
