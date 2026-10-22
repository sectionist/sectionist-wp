# 4. Data format, storage and migrations

Status: accepted

## Context

Gutenberg serializes blocks as HTML comments in `post_content`. The stored HTML must match the block code, which causes validation errors and forces `deprecated` entries. Sectionist wants data without markup.

Sectionist replaces the editor for its post types. Their content is the sections, so it belongs in `post_content`. A post with empty `post_content` and content in post meta works against the WordPress data model: revisions, export/import and cloning ignore meta.

## Decision

**Storage.** The JSON document is stored as raw JSON in `post_content`, without markers and without rendered HTML. The classic editor and Gutenberg are disabled for the affected post types (`remove_post_type_support( $type, 'editor' )`), so nothing else writes the field.

- Writes go through the filter `wp_insert_post_data`, never through `wp_update_post` inside `save_post` (infinite loop).
- Values are passed with `wp_slash( wp_json_encode( ... ) )`.
- Core runs kses on `post_content` before `wp_insert_post_data`, so our JSON, set in that filter, is not mangled. Every section type sanitizes its own data, and the nonce, capability and autosave checks stay in place. Without a valid request the stored content is kept.
- `the_content()` is not used. `sectionist_render()` reads the document from `post_content` and renders it. The `Repository` interface is the seam, `PostContentRepository` implements it (`load()` and `encode()`; writing happens in the filter).
- WordPress revisions of `post_content` replace the custom revision storage.
- Writes from outside the editor (REST, import, `wp_update_post`, other plugins) are accepted only if they are a valid Sectionist document and the stored document is not newer than the code supports. Anything else is replaced by the stored content (empty for new posts).
- If another plugin re-enables the content editor for our post types, an admin notice warns about it.

**Format.** JSON with a single version at the container level:

```json
{
  "schema_version": 1,
  "sections": [
    { "id": "a1b2c3", "type": "text", "active": true, "background": "", "data": { "body": "<h2>Hello</h2><p>...</p>" } }
  ]
}
```

- No per-section version. It can be added later, a missing field then means version 1.
- Unknown section types are preserved unchanged.
- Only rich-text fields contain markup: a sanitized HTML fragment inside the JSON. All other fields are plain values.
- Optional values get defaults when read.
- Container fields per section: `id`, `type`, `active` (missing means true), `background` (a single theme-defined key, never a color), `data`. They are additive, so `schema_version` stays 1.
- Inactive sections are stored, never deleted. Public read paths use `Document::activeSections()`, and the renderer checks `active` as a second safeguard. Only the admin editor and preview read all sections.
- `background` keys are offered by the active theme through the filter `sectionist/backgrounds` (entries with `key`, `label`, `color`). Without entries, no choice is shown. The server checks only the key format, so a previously stored key survives if the theme stops offering it. Themes must ignore unknown keys.
- Storage stays a single JSON blob in `post_content`. No custom table and no index until cross-post queries are needed (a later additive index is possible, the repository is the only place to change).

**Migrations.**

- The option `sectionist_db_version` is compared with the version the code expects on `admin_init`.
- On mismatch the migration chain runs in batches, guarded by a lock (transient). Afterwards the option is raised.
- Each step is a pure function (array in, array out), never deletes data, and has fixture tests per version.
- A `schema_version` newer than the code is never overwritten.
- In `v0.1.0` the chain is empty. The in-memory read bridge for not-yet-migrated data is built with the first real migration.

**Search and feeds (open).** Core search, excerpts, SEO plugins and RSS see raw JSON in `post_content`. Filters for `get_the_excerpt` and feeds, and a `searchableText()` method on section types, may come later.

**Plugin removed (open).** Without the plugin, the raw JSON shows up in the theme output via `the_content()`. This is visible and therefore easy to notice.

## Consequences

- Clean separation of data and presentation, easy to test.
- Core WordPress search matches the JSON text, and excerpts show raw JSON until filters exist.
- Data lives where WordPress expects content, so export, import and revisions work without extra code.
- Between a plugin update and the end of migration, old data is not readable by the new code.

## Rejected alternatives

- **HTML in `post_content` (Gutenberg style):** couples data to markup.
- **JSON in post meta with empty `post_content`:** misuses meta as the content field, and revisions, export and cloning ignore it.
- **JSON in meta plus rendered HTML in `post_content`:** two storage locations, and the HTML must be regenerated whenever views change.
- **Per-section versions now:** an abstraction for a hypothetical case.
- **Lazy migration on read:** mixes reading and writing.
- **WP-CLI-only migration:** not how WordPress users expect updates to work.
