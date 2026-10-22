# 6. Rendering contract

Status: accepted

## Context

The plugin must not decide how content looks, but themes need a predictable way to render and override sections.

## Decision

Rendering is data in, HTML out. For each section the view is resolved in this order:

1. Active theme
2. Plugin fallback

The plugin knows no templating engine. Starter themes provide the views in their own technology. The plugin fallback is plain PHP and minimal. Editor previews use server-side rendering over REST.

## Consequences

- Themes override any view and add their own types.
- The plugin renders something sensible without the theme.
- The plugin fallback must be kept in sync with the contract.

## Rejected alternatives

- **Templates inside the plugin only:** no override path.
- **Client-side rendering for preview:** duplicates view logic.
