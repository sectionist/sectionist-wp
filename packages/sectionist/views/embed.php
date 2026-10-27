<?php

declare(strict_types=1);
use Sectionist\Sections\Cta;
use Sectionist\Storage\Section;

/**
 * Plugin fallback view for the "embed" section type.
 *
 * The core never calls oEmbed: it renders the body, the stored URL as a plain
 * link plus the caption, and the call to action. Themes may resolve the URL and
 * add their own consent handling.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$url = is_string($data['url'] ?? null) ? $data['url'] : '';
$caption = is_string($data['caption'] ?? null) ? $data['caption'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
if ($url === '' && trim(wp_strip_all_tags($body)) === '' && ! Cta::renders($cta)) {
    return;
}
?>
<section class="sectionist-section sectionist-section--embed">
<div class="sectionist-section__content">
<?php if ($body !== '') { ?>
    <?php echo wp_kses_post($body); ?>
<?php } ?>
<?php if ($url !== '') { ?>
    <figure>
        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($url); ?></a>
<?php if ($caption !== '') { ?>
        <figcaption><?php echo esc_html($caption); ?></figcaption>
<?php } ?>
    </figure>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</div>
</section>
