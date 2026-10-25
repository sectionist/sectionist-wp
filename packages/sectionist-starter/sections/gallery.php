<?php

declare(strict_types=1);

use Sectionist\Storage\Section;

/**
 * Theme view for the "gallery" section type. Overrides the plugin fallback.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
$images = is_array($data['images'] ?? null) ? $data['images'] : [];
$background = ($data['media'] ?? 'none') === 'background' ? (string) $section->background : '';
?>
<section class="section section--gallery<?php echo $background !== '' ? ' section--bg-'.esc_attr($background) : ''; ?>">
<div class="section__content">
<?php echo wp_kses_post($body); ?>
<?php if ($images !== []) { ?>
<ul class="section__gallery">
<?php foreach ($images as $image) { ?>
<?php if (! is_array($image)) {
    continue;
} ?>
<li>
<figure>
<?php echo wp_get_attachment_image((int) ($image['id'] ?? 0), 'large'); ?>
<?php if (($image['caption'] ?? '') !== '') { ?><figcaption><?php echo esc_html((string) $image['caption']); ?></figcaption><?php } ?>
</figure>
</li>
<?php } ?>
</ul>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</div>
</section>
