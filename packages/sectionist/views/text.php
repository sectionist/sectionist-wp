<?php

declare(strict_types=1);

/**
 * Plugin fallback view for the "text" section type.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var array<string, mixed> $section The whole section (id, type, data).
 */

$body = is_string($data['body'] ?? null) ? $data['body'] : '';
?>
<section class="sectionist-section sectionist-section--text">
<?php if ($body !== '') : ?>
    <?php echo wp_kses_post($body); ?>
<?php endif; ?>
</section>
