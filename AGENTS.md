# AGENTS.md

Binding working guide for AI agents and developers. Items marked **planned** do not exist yet. Items marked **TODO** are open or inconsistent.

## Project

- **Sectionist** — "Another Wordpress Editor Plugin".
- Gutenberg-free, code-first section editor as a WordPress plugin. Structured content, versioned data format (`schema_version`). Rendering lives in the theme.
- Audience: developers (by developers, for developers) and editors who use the editor daily without code. No packaged end-user product, no support promise.
- Free and open source, no premium dependencies. License: GPL-3.0-or-later.
- WordPress-only. No CMS portability. The core uses WordPress APIs directly (hooks, post meta, REST, nonces, capabilities).
- Non-goals: page builder, Gutenberg, design assumptions in the core, abstractions for hypothetical other platforms.
- Greenfield. No legacy, no backward compatibility with any earlier system. Never reference or copy an earlier project.

## Repo layout

GitHub: `sectionist/sectionist-wp` (monorepo, source of truth), default branch `main`.

Current state:

- Root: `composer.json` (dev tools only, type `project`, never published), `composer.lock`, `.editorconfig`, `.gitignore`, `README.md`, `LICENSE`.
- `packages/sectionist/`: the plugin.
  - Composer name `sectionist/sectionist`, type `wordpress-plugin`, slug `sectionist`.
  - Main file `sectionist.php`, text domain `sectionist`, PSR-4 `Sectionist\` -> `src/`.
- `packages/sectionist-starter/`: the first starter theme (classic, native PHP). `packages/dump-theme` is an interim theme.
- `docs/` (`vision.md`, `adr/`), `.github/workflows/` (CI, release via semantic-release).
- Planned: more starter themes under `packages/` for other technologies.

- No `-core` suffix while there is only one plugin.
- Split repos for Packagist (`git subtree split` or splitsh-lite): planned, only on real Composer need. Packagist vendor `sectionist` is not registered yet.

## Conventions

- PHP: the plugin currently requires `>=8.5` (`packages/sectionist/composer.json`, `Requires PHP: 8.5` in the plugin header). Local PHP is 8.5.
- `declare(strict_types=1)` in every PHP file. PSR-4. File name equals class name (mind case, macOS).
- Core: native PHP. No framework, no `illuminate/*`. New Composer dependencies in the plugin need a justification.
- Hooks are prefixed `sectionist/`, e.g. `sectionist/sections/register`.
- Security: nonce, capability and autosave checks on save; escape on output; one sanitizer per section type.
- i18n: source strings in English via WordPress i18n functions, text domain `sectionist`.
- Null-safety: fallbacks for optional values; preserve unknown section types instead of deleting them.
- Language: talk to the maintainer in German. Code, identifiers, comments and docs in the repo in English.
- Implemented:
  - Storage: JSON with `schema_version` in `post_content` (ADR 0004), migration runner, `PostContentRepository`.
  - Rendering contract: data in, HTML out (ADR 0006, 0008). Resolution order: active theme, plugin fallback. The plugin knows no templating engine.
  - Starter themes: complete themes a project copies and owns. Technology choices live only in the individual starter theme.
  - Admin UI: declarative field schema in PHP, generic renderer, slim Preact editor (Vite). Preview via server-side rendering over REST.
  - Theme manifest `sectionist.json` (ADR 0008).

## Tooling

- Root dev tools: PHPUnit ^13.4 (needs PHP >= 8.4), PHPStan ^2.3, Laravel Pint ^1.32.
- Root Composer scripts: `test` (`phpunit`), `lint` (`pint --test`), `lint:fix` (`lint`), `analyse` (`phpstan analyse`).
- `composer.lock` is committed. `vendor/` and `node_modules/` are never committed.
- GitHub Actions: `ci.yml`, `release.yml`. Planned: PHP matrix. PHPUnit 13 does not run on PHP 8.3.

## Dev environment

- The dev WordPress lives outside this repo, in a separate Docker setup (Apache/PHP and MariaDB). Its location is local to each developer.
- The plugin is mounted into the container via `docker-compose.yml`:
  `<path-to-repo>/packages/sectionist:/var/www/html/wp-content/plugins/sectionist`
- WP-CLI runs in the container, e.g. `wp plugin list` (through a local wrapper or `docker compose exec`).
- After namespace or autoload changes, run `composer dump-autoload` in `packages/sectionist`.
- Never copy the dev environment into this repo (`dev/` is in `.gitignore`).

## Agent rules

- Work in small steps. Answer briefly, one step at a time.
- Question naming and structure decisions (slug, namespace, package name, folders) before implementing them. State the cost of reversing. No decisions on stock without a concrete reason.
- No abstractions for hypothetical future cases.
- Starter themes are plain, standard WordPress themes: theme bootstrap (supports, menus, assets, template hierarchy) lives directly in `functions.php`. No `inc/`, `setup.php` or similar split files. Keep it simple, do not reinvent the wheel.
- No commits, pushes or Packagist actions without explicit instruction. Commit messages in Conventional Commits style (`feat(scope): ...`).
- When changing code, immediately remove anything it makes unused.
- UI elements used in at least two places live as a component in `packages/sectionist/assets/components/`. Before writing new UI markup, check the existing components there (e.g. `ButtonGroup`, `Field`, `ImageField`, `CtaField`, `MediaField`) and reuse them. Never copy markup.
- Only state what is true in the repo; do not invent.

## Roadmap

1. Vision and ADRs (done).
2. Tooling and CI (done).
3. Core without UI (done).
4. Vertical slice (done): type `text` through to rendering in the starter theme.
5. Expansion (planned): field types, more section types (hero, image), full-page editor layout, a second starter theme.
6. Before v1.0.0 (planned): one sweep for plain-text access to section content, once the set of section types is stable:
   - Search: it runs on the raw JSON in `post_content`.
   - Feeds: RSS probably outputs the JSON (unchecked).
   - Excerpt: `Excerpt` only uses the `body` field, not lists or FAQ.
   - Open design question: plain-text source (renderer with plugin fallback vs. theme) and an index (e.g. post meta `_sectionist_text`), including reindex of existing pages and updates when a central FAQ changes.

## Open items

- TODO: Editor UX leftover: move animation when reordering sections is still open (drag & drop and open/close animation are done).
- TODO: `RichTextEditor` options (toolbar, extensions) are global. Add a per-field option only when a section type needs a restricted variant.
- TODO: `LinkModal.tsx` still uses its own label/input markup instead of the `Field` components. Low priority.
- TODO: Badge warning colors and `#fff` in `assets/style.css` are hard-coded instead of tokens. Low priority.
- TODO: Add option for links to open in a new tab.
- TODO (to be discussed, lower priority): `columns` as a structuring element (changes the data format, needs an ADR first), `embed` (oEmbed, consent in the theme), `html`/code (only with `unfiltered_html`), uninstall and export (what happens to the JSON when the plugin is deactivated; related to the plain-text sweep before v1.0.0).
