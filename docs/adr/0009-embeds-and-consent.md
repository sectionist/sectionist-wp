# 9. Embeds and consent

Status: accepted

## Context

An embed section references third-party media (video, audio, slides) through an oEmbed provider. WordPress can resolve the URL server-side (`wp_oembed_get()` or `$wp_embed->shortcode()`) and returns provider HTML, usually an iframe. Loading that iframe in the browser sends the visitor's IP address and referrer to the provider. At the same time the plugin must not decide how content looks (ADR 0006) and must not hold design values (ADR 0008).

## Decision

- **Storage.** The section type `embed` stores a `url` and a `caption`. `url` is an absolute HTTPS URL, sanitized with `esc_url_raw(..., ['https'])`; `caption` is plain text. No provider name, provider ID or embed HTML is stored. The type and its fields are additive, so `schema_version` stays 1.
- **The core does not embed.** The plugin never calls oEmbed and never outputs third-party iframes or scripts. Its fallback view renders the URL as a plain link plus the caption.
- **Rendering and consent live in the theme.** A theme view may resolve oEmbed and decides how consent works. The starter theme resolves the oEmbed HTML server-side (cached by WordPress in post meta), keeps the iframe inert inside a `<template>`, and inserts it only after the visitor clicks. The extracted iframe is sanitized with `wp_kses()` against an iframe attribute allowlist, only an HTTPS `src` is accepted, and `loading="lazy"` is added. Without JavaScript the visitor gets a link to the provider.
- **Iframe only in the starter theme.** The starter theme uses oEmbed output only when it contains an iframe. Other provider output renders as a link. Providers that require their own script are out of scope for the starter theme.
- **No consent state is stored.** Consent is per page view. The plugin keeps no preference, cookie or option for it.
- **No provider allowlist.** Editors can use any URL that survives sanitizing. The theme decides whether it can render it.

## Consequences

- A theme without embed support still renders the section as a link, so the content is never lost.
- The starter theme accepts only HTTPS iframe sources, so providers that return an HTTP iframe (for example on an HTTP-served site) fall back to the link.
- The editor preview shows the consent gate's message and provider link, not the loaded embed, because the theme script is a front-end asset.
- The server fetches provider metadata while rendering the page. WordPress caches the result per post when the theme resolves it through `$wp_embed->shortcode()`, so the fetch and cache are reliable on the front end. The REST preview has no post context and is not guaranteed to hit the post-meta cache.
- A theme that embeds without consent makes that privacy decision itself. The core cannot enforce consent.

## Rejected alternatives

- **Plugin-side oEmbed and iframe:** puts a privacy decision and provider markup into the core and breaks the rendering contract.
- **Storing provider HTML or provider IDs:** couples stored data to providers and needs migrations when provider markup changes.
- **Provider allowlist in the plugin:** needs maintenance and still does not solve consent.
- **Consent stored in an option or cookie:** consent is per visitor and per page view, not a site setting.
