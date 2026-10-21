# 3. Native PHP in the core

Status: accepted

## Context

The plugin runs inside arbitrary WordPress installs. Heavy dependencies raise conflict risk and installation size.

## Decision

The plugin core is native PHP 8.5 with PSR-4 autoloading. No Laravel and no `illuminate/*`. New Composer dependencies in the plugin require a justification.

## Consequences

- A small footprint and few dependency conflicts.
- Some utilities must be written by hand.
- Laravel is allowed only in the theme, via Acorn.

## Rejected alternatives

- **Acorn or Laravel in the plugin:** couples the plugin to a theme stack and adds weight.
- **Supporting older PHP versions:** PHP 8.5 is the minimum, which allows modern language features.
