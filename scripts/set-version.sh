#!/usr/bin/env bash
# Writes the release version into the plugin header and package.json.
set -euo pipefail

version="${1:?usage: set-version.sh <version>}"
root="$(cd "$(dirname "$0")/.." && pwd)"
plugin="$root/packages/sectionist"

tmp="$(mktemp)"
sed -E "s/^( \* Version:).*/\1 ${version}/" "$plugin/sectionist.php" > "$tmp"
cat "$tmp" > "$plugin/sectionist.php"
rm -f "$tmp"

(cd "$plugin" && npm version "$version" --no-git-tag-version --allow-same-version >/dev/null)
