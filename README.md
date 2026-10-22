# Sectionist

Another WordPress editor plugin: a Gutenberg-free, code-first section editor. Requires WordPress 6.4 and PHP 8.5.

## Known limitations

- **Search:** `post_content` stays empty, so the WordPress search does not find text inside sections.
- **Autosave:** Autosave stores the title but not the sections. The editor warns before you leave with unsaved section changes.
- **Revisions:** Restoring a revision restores the whole sections document (a snapshot). Revisions created before this feature have no sections and leave the current sections untouched.
- **Collapse state:** The open/closed state of sections is not persisted.
- **Preview:** The preview does not update live and shows the plugin fallback HTML without theme styles.
