<?php

declare(strict_types=1);
use Sectionist\Sections\Media;
use Sectionist\Storage\Section;

/**
 * Plugin fallback view for the "text" section type.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
$media = is_array($data['image'] ?? null) ? $data['image'] : [];
$image = Media::id($media);
$position = is_string($media['position'] ?? null) ? $media['position'] : 'right';
$before = in_array($position, ['left', 'top'], true);
?>
<section class="sectionist-section sectionist-section--text<?php echo $image > 0 ? ' sectionist-section--image sectionist-section--image-'.esc_attr($position) : ''; ?>">
<?php if ($image > 0 && $before) { ?>
    <figure class="sectionist-section__media"><?php echo wp_get_attachment_image($image, 'large'); ?></figure>
<?php } ?>
<div class="sectionist-section__content">
<?php if ($body !== '') { ?>
    <?php echo wp_kses_post($body); ?>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</div>
<?php if ($image > 0 && ! $before) { ?>
    <figure class="sectionist-section__media"><?php echo wp_get_attachment_image($image, 'large'); ?></figure>
<?php } ?>
</section>
