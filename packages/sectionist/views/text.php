<?php

declare(strict_types=1);

/**
 * Plugin fallback view for the "text" section type.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var array<string, mixed> $section The whole section (id, type, data).
 */

$heading = is_string($data['heading'] ?? null) ? $data['heading'] : '';
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
?>
<section class="sectionist-section sectionist-section--text">
<?php if ($heading !== '') : ?>
    <h2><?php echo esc_html($heading); ?></h2>
<?php endif; ?>
<?php if ($body !== '') : ?>
    <?php echo wp_kses_post($body); ?>
<?php endif; ?>
</section>
