# 8. Theme contract

Status: accepted

## Context

Themes render sections and style them. The plugin must work with any theme technology and must not hold design values.

## Decision

The contract between plugin and active theme:

- **Views:** `sections/<type>.php` in the theme root overrides the plugin fallback `views/<type>.php`. Shared markup lives in `sections/partials/` (e.g. `partials/cta.php`). A view receives the `Section` object in `$section` (`id`, `type`, `active`, `background`, `data`) and `$data`.
- **Template tag:** `sectionist_render(?int $postId = null)` echoes all sections of a post in editor order. The theme places the block, the plugin decides the order.
- **Manifest:** `sectionist.json` in the theme root, with a `version`.
  - `backgrounds`: list of `{key, label}`. Only the key is stored. No colors in the plugin or the manifest.
  - `editor_styles`: theme-relative paths or absolute URLs of stylesheets the editor loads, so it shows the same `.section--bg-<key>` colors as the frontend. Local files are versioned by modification time; paths with `..` are ignored.
- **Filters:** `sectionist/backgrounds` adjusts the background list, `sectionist/editor_styles` adds or replaces editor stylesheets (hashed build output, CDN URLs).
- **Failure mode:** a missing or broken manifest means no backgrounds and no editor styles, never an error.
- **Design values live in theme CSS only.** Any CSS framework works if the class `.section--bg-<key>` exists in the built CSS (Tailwind: `@layer components` or `safelist`).

## Consequences

- Themes in any technology (e.g native PHP, Twig, Tailwind builds) can implement the contract. Non-PHP view engines need a thin `sections/<type>.php` or a future filter for the view extension.
- The manifest is a versioned contract and needs migration rules on breaking changes.
- Headless frontends are not covered; they would need a data API (separate ADR when needed).

## Rejected alternatives

- **Colors in `sectionist.json`:** makes the plugin a design system and breaks utility frameworks.
- **Wrapper templates (`page.php`, `single.php`) in the plugin:** the plugin would decide theme markup.
