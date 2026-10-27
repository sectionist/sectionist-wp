<?php

declare(strict_types=1);

use Sectionist\Sections\Cta;
use Sectionist\Storage\Section;

/**
 * Theme view for the "embed" section type. Overrides the plugin fallback.
 *
 * Renders the body before the consent gate, then resolves the URL through
 * WordPress oEmbed (cached by WordPress on the front end) and keeps the provider
 * iframe inert inside a template until the visitor consents. Provider output
 * without an iframe is rendered as a plain link. A call to action renders after
 * the embed.
 *
 * @var array<string, mixed> $data Sanitized section data.
 * @var Section $section The whole section (id, type, active, background, data).
 */
$body = is_string($data['body'] ?? null) ? $data['body'] : '';
$url = is_string($data['url'] ?? null) ? $data['url'] : '';
$caption = is_string($data['caption'] ?? null) ? $data['caption'] : '';
$cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
$background = ($data['media'] ?? 'none') === 'background' ? (string) $section->background : '';
if ($url === '' && trim(wp_strip_all_tags($body)) === '' && ! Cta::renders($cta)) {
    return;
}

$iframe = '';
$sourceHost = '';
if ($url !== '' && isset($GLOBALS['wp_embed']) && $GLOBALS['wp_embed'] instanceof WP_Embed) {
    $html = $GLOBALS['wp_embed']->shortcode([], $url);
    if (is_string($html) && preg_match('#<iframe\b.*?</iframe>#is', $html, $matches) === 1) {
        $candidate = wp_kses($matches[0], [
            'iframe' => [
                'src' => true,
                'title' => true,
                'width' => true,
                'height' => true,
                'allow' => true,
                'allowfullscreen' => true,
                'frameborder' => true,
                'referrerpolicy' => true,
                'loading' => true,
                'class' => true,
            ],
        ], ['https']);

        $src = '';
        if (preg_match('/<iframe\b[^>]*?(?<![\w-])src\s*=\s*(["\'])(.*?)\1/is', $candidate, $srcMatch) === 1) {
            $src = html_entity_decode($srcMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $scheme = wp_parse_url($src, PHP_URL_SCHEME);
        $srcHost = wp_parse_url($src, PHP_URL_HOST);
        if (is_string($scheme) && strtolower($scheme) === 'https' && is_string($srcHost) && $srcHost !== '') {
            $iframe = $candidate;
            $sourceHost = $srcHost;
            if (preg_match('/(?:^|\s)loading\s*=/i', $iframe) !== 1) {
                $iframe = (string) preg_replace('/<iframe\b/i', '<iframe loading="lazy"', $iframe, 1);
            }
        }
    }
}
?>
<section class="section section--embed<?php echo $background !== '' ? ' section--bg-'.esc_attr($background) : ''; ?>">
<div class="section__content">
<?php echo wp_kses_post($body); ?>
<?php if ($url !== '') { ?>
<figure class="section__embed">
    <?php if ($iframe !== '') { ?>
        <div class="section__embed-consent" data-sectionist-embed>
            <p><?php
                printf(
                    /* translators: %s: host name of the embedded media provider, e.g. www.youtube.com. */
                    esc_html__('This embed loads content from %s. It is only requested after you allow it.', 'sectionist-starter'),
                    esc_html($sourceHost),
                );
        ?></p>
            <button type="button" data-sectionist-embed-load
                    hidden><?php esc_html_e('Load embed', 'sectionist-starter'); ?></button>
            <a href="<?php echo esc_url($url); ?>" target="_blank"
               rel="noopener noreferrer"><?php esc_html_e('Open on provider', 'sectionist-starter'); ?></a>
            <template data-sectionist-embed-template><?php echo $iframe; ?></template>
        </div>
    <?php } else { ?>
        <a href="<?php echo esc_url($url); ?>" target="_blank"
           rel="noopener noreferrer"><?php echo esc_html($url); ?></a>
    <?php } ?>
    <?php if ($caption !== '') { ?>
        <figcaption class="section__embed-caption"><?php echo esc_html($caption); ?></figcaption>
    <?php } ?>
</figure>
<?php } ?>
<?php require __DIR__.'/partials/cta.php'; ?>
</div>
</section>
