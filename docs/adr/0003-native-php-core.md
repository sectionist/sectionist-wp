# 3. Native PHP in the core

Status: accepted

## Context

The plugin runs inside arbitrary WordPress installs. Heavy dependencies raise conflict risk and installation size.

## Decision

The plugin core is native PHP (minimum 8.3, only versions that still receive security patches) with PSR-4 autoloading. No Laravel and no `illuminate/*`. New Composer dependencies in the plugin require a justification.

## Consequences

- A small footprint and few dependency conflicts.
- Some utilities must be written by hand.
- Other technologies are allowed only in individual starter themes.

## Rejected alternatives

- **A specific theme framework in the plugin:** couples the plugin to a theme stack and adds weight.
- **Supporting older PHP versions:** PHP 8.3 is the minimum, because older branches no longer get security patches. Raise it when a newer language feature is used or a branch loses support.
