# Sectionist — Vision

**Another WordPress Editor Plugin.**

Sectionist is a Gutenberg-free, code-first section editor for WordPress. Content is stored as structured, versioned data. Rendering lives in the theme.

## Audience

Sectionist has two audiences with different roles.

- **Developers** install, configure and extend it. They declare section types and fields in PHP, write the views in the theme and define what editors may do. Sectionist is built by developers, for developers.
- **Editors** use it every day. They create pages and fill sections with content, without writing code. The editing experience must be usable without technical knowledge. Developers decide what editors can choose, editors never need to touch code.

Sectionist is not a packaged end-user product. There is no support promise, and editors get their help from the developers who set up their site.

## What Sectionist is, and what it is not

Sectionist is a page creator, not a page builder.

1. A user creates a normal WordPress page.
2. The user builds its content from sections: adds the needed sections, orders them and fills their fields.
3. Most sections span a full row. Grid sections with nested children may come later and are not a topic now.
4. Text fields offer a slim rich-text editor with a limited feature set. Users cannot set colors, arbitrary fonts or font sizes.

The user only works with building blocks the developer declares. Design decisions live in the theme. Where a user may choose a variation, the developer declares a select field with fixed values (for example `variant: light|dark`). The theme decides how each value looks. There are no free color values and no generic style editor.

## Principles

- **Code-first.** Section types and their fields are declared in PHP, not clicked together in a UI.
- **Structured content.** Content is data (JSON with a `schema_version`), not HTML.
- **Data and presentation are separate.** The plugin delivers data and an editor. The theme decides how it looks.
- **Modern stack.** Native PHP (8.3 or newer) in the plugin. Sage, Blade, Tailwind v4 and Vite in the parent theme.
- **No lock-in.** Free and open source (GPL-3.0-or-later), no premium dependencies, a documented data format, and unknown section types are preserved instead of deleted.

## Non-goals

- A page builder.
- Gutenberg or any block-editor integration.
- Design assumptions in the core.
- Portability to other CMSs, or abstractions for hypothetical platforms.
- Backward compatibility with any earlier system. This is a greenfield project.
