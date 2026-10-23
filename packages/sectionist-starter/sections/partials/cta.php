<?php

declare(strict_types=1);

use Sectionist\Sections\Cta;

/**
 * Theme partial for a call-to-action. Reusable by any section view.
 *
 * @var array<string, mixed> $cta Sanitized CTA value (see Cta::sanitize()).
 */
$url = Cta::url($cta);
$label = is_string($cta['label'] ?? null) ? $cta['label'] : '';
if (($cta['active'] ?? false) !== true || $url === '' || $label === '') {
    return;
}
$style = ($cta['style'] ?? 'button') === 'link' ? 'link' : 'button';
?>
<div class="section-cta">
    <a class="section-cta__action section-cta__action--<?php echo esc_attr($style); ?>" href="<?php echo esc_url($url); ?>"<?php echo ($cta['new_tab'] ?? false) === true ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html($label); ?></a>
</div>
