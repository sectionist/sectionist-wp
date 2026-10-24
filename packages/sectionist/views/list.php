<?php

declare(strict_types=1);
use Sectionist\Storage\Section;

/**
 * Plugin fallback view for the "list" section type.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
$items = is_array($data['entries'] ?? null) ? $data['entries'] : [];
?>
<section class="sectionist-section sectionist-section--list">
<?php if ($body !== '') { ?>
    <?php echo wp_kses_post($body); ?>
<?php } ?>
<?php if ($items !== []) { ?>
    <ul class="sectionist-entries">
<?php foreach ($items as $item) { ?>
<?php if (! is_array($item)) {
    continue;
} ?>
        <li class="sectionist-entries__item">
            <?php if (($item['title'] ?? '') !== '') { ?><h3 class="sectionist-entries__title"><?php echo esc_html((string) $item['title']); ?></h3><?php } ?>
            <?php if (($item['text'] ?? '') !== '') { ?><p class="sectionist-entries__text"><?php echo nl2br(esc_html((string) $item['text'])); ?></p><?php } ?>
        </li>
<?php } ?>
    </ul>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</section>
