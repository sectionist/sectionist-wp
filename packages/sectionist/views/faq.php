<?php

declare(strict_types=1);

use Sectionist\Faq\Faq;
use Sectionist\Storage\Section;

/**
 * Plugin fallback view for the "faq" section type.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
$faq = is_array($data['faq'] ?? null) ? $data['faq'] : [];
$questions = Faq::entries($faq);
$level = Faq::questionLevel($body, $faq);
?>
<section class="sectionist-section sectionist-section--faq">
<?php if ($body !== '') { ?>
    <?php echo wp_kses_post($body); ?>
<?php } ?>
<?php foreach ($questions as $item) { ?>
    <details class="sectionist-faq__item">
        <summary class="sectionist-faq__question"><?php if ($level > 0) { ?><h<?php echo $level; ?>><?php echo esc_html($item['question']); ?></h<?php echo $level; ?>><?php } else { ?><?php echo esc_html($item['question']); ?><?php } ?></summary>
        <div class="sectionist-faq__answer"><?php echo wp_kses_post($item['answer']); ?></div>
    </details>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</section>
