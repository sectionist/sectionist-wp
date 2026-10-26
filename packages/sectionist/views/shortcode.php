<?php

declare(strict_types=1);
use Sectionist\Storage\Section;

/**
 * Plugin fallback view for the "shortcode" section type.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
$items = is_array($data['shortcodes'] ?? null) ? $data['shortcodes'] : [];
?>
<section class="sectionist-section sectionist-section--shortcode">
<?php if ($body !== '') { ?>
    <?php echo wp_kses_post($body); ?>
<?php } ?>
<?php foreach ($items as $item) { ?>
<?php if (! is_array($item) || ! is_string($item['code'] ?? null)) {
        continue;
    } ?>
    <div class="sectionist-shortcode"><?php echo do_shortcode($item['code']); ?></div>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</section>
