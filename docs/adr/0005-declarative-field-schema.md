# 5. Declarative field schema

Status: accepted

## Context

Each section type needs an editor form, validation and sanitizing. Hand-written forms per type are repetitive and hard to keep consistent. Editors fill these forms every day and must be able to use them without technical knowledge.

## Decision

A section type declares its fields as a schema in PHP. A generic renderer builds the admin form from it. The admin editor is a slim TypeScript app built with Preact and Vite. It uses neither `wp.element` nor `@wordpress/components`. `dist/` is not committed and is produced only by the release build. Each type has exactly one sanitizer. Types are registered via the hook `sectionist/sections/register`.

Two field kinds keep design out of the data:

- **Rich text.** A field type with a developer-declared whitelist of formatting (for example bold, italic, links, lists). The type's sanitizer enforces it server-side (for example via `wp_kses`), stripping inline styles and font information. The restriction is not UI-only.
- **Select with fixed values.** Variations such as `light|dark` are declared as options. There are no free color, font or size inputs.

**Rich-text editor: Tiptap.** The admin uses the Tiptap core (MIT). Rules:

- Only MIT-licensed `@tiptap/*` packages. Never `@tiptap-pro/*` and no cloud services. CI checks this.
- The editor is replaceable: a rich-text field receives and returns a plain HTML string. The data format does not depend on Tiptap.
- The server stays the source of truth and sanitizes with the field's whitelist.
- The formatting whitelist from the PHP schema configures the editor extensions.
- Fallback if the vendor risk materializes: ProseMirror directly (Tiptap is a layer on top of it) or a fork.
- Bundle size is measured in the prototype.
- In the vertical slice the rich-text field is first a `textarea`. Tiptap follows as its own sub-step.

## Consequences

- Adding a type means writing PHP only.
- The schema is the single source for form, defaults and sanitizing.
- Unusual UI needs may require extending the renderer.
- The plugin does not run from the repository without `npm run build`. Release ZIP and split repo must contain `dist/`.
- Tiptap is a dependency on a single vendor. Its core is MIT, and the rules above limit the exposure.

## Rejected alternatives

- **Custom form code per type:** inconsistent and repetitive.
- **Schema in JSON or YAML files:** loses code-first typing and IDE support.
- **A React-based editor in the Gutenberg style:** contradicts the Gutenberg-free goal.
- **Alpine:** becomes hard to maintain with editor state and later nested sections.
- **TinyMCE:** heavy and hard to embed in a Preact app.
- **Custom `contenteditable`:** high risk (browser quirks, paste, undo, accessibility).
- **Lexical, Quill:** React-oriented or less maintained.
