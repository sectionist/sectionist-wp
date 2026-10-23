# Sectionist Starter Theme

Minimal classic theme for the [Sectionist](../sectionist) plugin. Copy it, rename it (folder, `Theme Name`, text domain), and own it.

## What it does

- `templates/page.php` and `templates/single.php` output only `sectionist_render()` and `comments_template()`. Everything visible in the body comes from sections.
- Header, footer, navigation, comments, archives, search and 404 are classic WordPress.

## Structure

```
style.css  functions.php  index.php  sectionist.json   (required root files)
templates/  index.php  page.php  single.php  archive.php  search.php  404.php
layouts/    header.php  footer.php  comments.php  content-excerpt.php
sections/   text.php  partials/cta.php
resources/  css/theme.css
```

`functions.php` makes WordPress look up the template hierarchy in `templates/`. The hierarchy itself is unchanged (`page-{slug}.php`, `single-{type}.php`, ...), so add new templates there.

## What it does not do (on purpose)

- No page title, no post thumbnail, no `the_content()`. The editor places the `h1` in a section.

## sectionist.json

The theme manifest, comparable to `theme.json`. The plugin reads it from the active theme root.

```json
{
	"version": 1,
	"editor_styles": ["resources/css/theme.css"],
	"backgrounds": [{ "key": "light-gray", "label": "Light gray" }]
}
```

- `backgrounds`: choices offered per section. Only the key is stored. The theme styles `.section--bg-<key>` in its own CSS; the plugin knows no colors.
- `editor_styles`: stylesheets (theme-relative paths or absolute URLs) the editor loads to show those colors. For hashed build output, use the filter `sectionist/editor_styles`.

## Section views

Views are resolved in this order: `sections/<type>.php` in the active theme, then the plugin fallback.

- `sections/text.php` overrides the `text` view. It receives `$data` and `$section`.
- `sections/partials/cta.php` is included by the theme's `text.php`. It renders the CTA field of that section.
