<?php

declare(strict_types=1);
use Sectionist\Storage\Section;

/**
 * Plugin fallback view for the "embed" section type.
 *
 * The core never calls oEmbed: it renders the stored URL as a plain link.
 * Themes may resolve the URL and add their own consent handling.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$url = is_string($data['url'] ?? null) ? $data['url'] : '';
$caption = is_string($data['caption'] ?? null) ? $data['caption'] : '';
if ($url === '') {
    return;
}
?>
<section class="sectionist-section sectionist-section--embed">
    <figure>
        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($url); ?></a>
<?php if ($caption !== '') { ?>
        <figcaption><?php echo esc_html($caption); ?></figcaption>
<?php } ?>
    </figure>
</section>
