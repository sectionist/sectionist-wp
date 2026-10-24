<?php

declare(strict_types=1);

use Sectionist\Storage\Section;

/**
 * Theme view for the "list" section type. Overrides the plugin fallback.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
$items = is_array($data['entries'] ?? null) ? $data['entries'] : [];
$background = ($data['media'] ?? 'none') === 'background' ? (string) $section->background : '';
?>
<section class="section section--list<?php echo $background !== '' ? ' section--bg-'.esc_attr($background) : ''; ?>">
<div class="section__content">
<?php echo wp_kses_post($body); ?>
<?php if ($items !== []) { ?>
<ul class="section__entries">
<?php foreach ($items as $item) { ?>
<?php if (! is_array($item)) {
    continue;
} ?>
<li>
<?php if (($item['title'] ?? '') !== '') { ?><h3><?php echo esc_html((string) $item['title']); ?></h3><?php } ?>
<?php if (($item['text'] ?? '') !== '') { ?><p><?php echo nl2br(esc_html((string) $item['text'])); ?></p><?php } ?>
</li>
<?php } ?>
</ul>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</div>
</section>
