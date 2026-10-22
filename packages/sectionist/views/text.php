<?php

declare(strict_types=1);
use Sectionist\Storage\Section;

/**
 * Plugin fallback view for the "text" section type.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
?>
<section class="sectionist-section sectionist-section--text">
<?php if ($body !== '') { ?>
    <?php echo wp_kses_post($body); ?>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</section>
