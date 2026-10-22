# 1. Plugin and starter themes

Status: accepted

## Context

Content structure must survive a theme change, but presentation needs a templating layer. Mixing both in one package couples data to design.

## Decision

The plugin `sectionist/sectionist` owns types, storage, editor and the render API. Presentation lives in starter themes: complete themes that a project copies and owns. We ship one starter theme first and add more over time to cover different technologies.

## Consequences

- The plugin knows no templating engine and no design.
- Technology choices exist only in the individual starter themes.
- Each starter theme is versioned independently of the plugin.
- A project owns its theme after copying it; there is no update coupling.

## Rejected alternatives

- **Plugin only, with built-in templates:** design assumptions leak into the core.
- **Theme only:** content would be lost on theme switch.
