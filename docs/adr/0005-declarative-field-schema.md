# 5. Declarative field schema

Status: accepted

## Context

Each section type needs an editor form, validation and sanitizing. Hand-written forms per type are repetitive and hard to keep consistent. Editors fill these forms every day and must be able to use them without technical knowledge.

## Decision

A section type declares its fields as a schema in PHP. A generic renderer builds the admin form from it. The admin editor is a slim JavaScript app (Preact or Alpine, built with Vite). Each type has exactly one sanitizer. Types are registered via the hook `sectionist/sections/register`.

Two field kinds keep design out of the data:

- **Rich text.** A field type with a developer-declared whitelist of formatting (for example bold, italic, links, lists). The type's sanitizer enforces it server-side (for example via `wp_kses`), stripping inline styles and font information. The restriction is not UI-only. Which editor provides it in the admin (for example TinyMCE or a small custom editor) is open until the admin editor is planned.
- **Select with fixed values.** Variations such as `light|dark` are declared as options. There are no free color, font or size inputs.

## Consequences

- Adding a type means writing PHP only.
- The schema is the single source for form, defaults and sanitizing.
- Unusual UI needs may require extending the renderer.

## Rejected alternatives

- **Custom form code per type:** inconsistent and repetitive.
- **Schema in JSON or YAML files:** loses code-first typing and IDE support.
- **A React-based editor in the Gutenberg style:** contradicts the Gutenberg-free goal.
