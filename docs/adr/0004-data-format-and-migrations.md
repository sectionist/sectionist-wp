# 4. Data format, storage and migrations

Status: accepted

## Context

Gutenberg serializes blocks as HTML comments in `post_content`. The stored HTML must match the block code, which causes validation errors and forces `deprecated` entries. Sectionist wants data without markup.

## Decision

**Storage.** One post meta key per post: `_sectionist_data`. `post_content` is not touched. Writes use `wp_slash( wp_json_encode( ... ) )`.

**Format.** JSON with a single version at the container level:

```json
{
  "schema_version": 1,
  "sections": [
    { "id": "a1b2c3", "type": "text", "data": { "heading": "Hello", "body": "..." } }
  ]
}
```

- No per-section version. It can be added later, a missing field then means version 1.
- Unknown section types are preserved unchanged.
- Only rich-text fields contain markup: a sanitized HTML fragment inside the JSON. All other fields are plain values.
- Optional values get defaults when read.

**Migrations.**

- The option `sectionist_db_version` is compared with the version the code expects on `admin_init`.
- On mismatch the migration chain runs in batches, guarded by a lock (transient). Afterwards the option is raised.
- Each step is a pure function (array in, array out), never deletes data, and has fixture tests per version.
- A `schema_version` newer than the code is never overwritten.
- In `v0.1.0` the chain is empty. The in-memory read bridge for not-yet-migrated data is built with the first real migration.

**Search (open).** `post_content` stays empty in `v0.1.0`. Frontend search, excerpts, SEO plugins and RSS do not see section content. A `searchableText()` method on section types may come later.

## Consequences

- Clean separation of data and presentation, easy to test.
- Core WordPress search does not find section content until the search question is decided.
- Between a plugin update and the end of migration, old data is not readable by the new code.

## Rejected alternatives

- **HTML in `post_content` (Gutenberg style):** couples data to markup.
- **Per-section versions now:** an abstraction for a hypothetical case.
- **Lazy migration on read:** mixes reading and writing.
- **WP-CLI-only migration:** not how WordPress users expect updates to work.
