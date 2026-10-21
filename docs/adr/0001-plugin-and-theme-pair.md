# 1. Plugin and theme as a pair

Status: accepted

## Context

Content structure must survive a theme change, but presentation needs a templating layer. Mixing both in one package couples data to design.

## Decision

Two packages. The plugin `sectionist/sectionist` owns types, storage, editor and the render API. A parent theme (e.g. Roots Sage with Acorn and Blade) owns the default views. Child themes override views and add types.

## Consequences

- The plugin knows no Blade and no design.
- Laravel (or any other technology) components exist only in the theme.
- Two things to version and release.

## Rejected alternatives

- **Plugin only, with built-in templates:** design assumptions leak into the core.
- **Theme only:** content would be lost on theme switch.
