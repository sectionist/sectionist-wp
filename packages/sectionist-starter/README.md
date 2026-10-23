# Sectionist Starter Theme

Minimal classic theme for the [Sectionist](../sectionist) plugin. Copy it, rename it (folder, `Theme Name`, text domain), and own it.

## What it does

- `page.php` and `single.php` output only `sectionist_render()` and `comments_template()`. Everything visible in the body comes from sections.
- Header, footer, navigation, comments, archives, search and 404 are classic WordPress.

## Structure

```
style.css  functions.php  index.php  page.php  single.php  archive.php  search.php  404.php
inc/       setup.php  assets.php
assets/css/main.css
parts/     header.php  footer.php  comments.php  content-excerpt.php
sections/  text.php  partials/cta.php
```

Only files WordPress looks up in the theme root stay there. Everything else is loaded via `get_template_part()` and `comments_template()`.

## What it does not do (on purpose)

- No page title, no post thumbnail, no `the_content()`. The editor places the `h1` in a section.

## Section views

Views are resolved in this order: `sections/<type>.php` in the active theme, then the plugin fallback.

- `sections/text.php` overrides the `text` view. It receives `$data` and `$section`.
- `sections/partials/cta.php` is included by the theme's `text.php`. It renders the CTA field of that section.
