<?php

declare(strict_types=1);

use Sectionist\Storage\Section;

/**
 * Theme view for the "text" section type. Overrides the plugin fallback.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
$background = (string) $section->background;
?>
<section class="section section--text<?php echo $background !== '' ? ' section--bg-'.esc_attr($background) : ''; ?>">
<?php echo wp_kses_post($body); ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</section>
