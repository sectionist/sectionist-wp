# 2. WordPress only

Status: accepted

## Context

Abstracting over several CMSs costs complexity and weakens the use of platform features.

## Decision

Sectionist targets WordPress only. The core uses WordPress APIs directly: hooks, post meta, REST, nonces and capabilities.

## Consequences

- Less code and no adapter layers.
- No CMS portability. Moving to another platform means a rewrite.

## Rejected alternatives

- **Platform-agnostic core with a WordPress adapter:** an abstraction for a hypothetical case.
