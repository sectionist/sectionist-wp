<?php

declare(strict_types=1);

use Sectionist\Faq\Faq;
use Sectionist\Storage\Section;

/**
 * Theme view for the "faq" section type. Overrides the plugin fallback.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
$faq = is_array($data['faq'] ?? null) ? $data['faq'] : [];
$questions = Faq::entries($faq);
$level = Faq::questionLevel($body, $faq);
$background = ($data['media'] ?? 'none') === 'background' ? (string) $section->background : '';
?>
<section class="section section--faq<?php echo $background !== '' ? ' section--bg-'.esc_attr($background) : ''; ?>">
<div class="section__content">
<?php echo wp_kses_post($body); ?>
<?php foreach ($questions as $item) { ?>
<details class="section__faq-item">
<summary><?php if ($level > 0) { ?><h<?php echo $level; ?>><?php echo esc_html($item['question']); ?></h<?php echo $level; ?>><?php } else { ?><?php echo esc_html($item['question']); ?><?php } ?></summary>
<div><?php echo wp_kses_post($item['answer']); ?></div>
</details>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</div>
</section>
