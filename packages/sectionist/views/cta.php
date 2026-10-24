<?php

declare(strict_types=1);
use Sectionist\Storage\Section;

/**
 * Plugin fallback view for the "cta" section type.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$image = ($data['media'] ?? 'none') === 'image' && is_int($data['backdrop'] ?? null) ? $data['backdrop'] : 0;
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
?>
<section class="sectionist-section sectionist-section--cta">
<?php if ($image > 0) { ?>
    <?php echo wp_get_attachment_image($image, 'full'); ?>
<?php } ?>
<?php if ($body !== '') { ?>
    <?php echo wp_kses_post($body); ?>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</section>
