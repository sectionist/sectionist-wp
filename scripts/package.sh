#!/usr/bin/env bash
# Builds build/sectionist-<version>.zip from packages/sectionist.
# Usage: package.sh [version]  (defaults to the Version header of sectionist.php)
set -euo pipefail

root="$(cd "$(dirname "$0")/.." && pwd)"
plugin="$root/packages/sectionist"
version="${1:-$(sed -nE 's/^ \* Version: *(.*)$/\1/p' "$plugin/sectionist.php")}"

(cd "$plugin" && npm ci && npm run build)
(cd "$plugin" && composer install --no-dev --optimize-autoloader --no-interaction)

stage="$(mktemp -d)"
trap 'rm -rf "$stage"' EXIT
mkdir -p "$stage/sectionist"

cp "$plugin/sectionist.php" "$plugin/composer.json" "$stage/sectionist/"
cp -R "$plugin/src" "$plugin/dist" "$plugin/vendor" "$stage/sectionist/"
[ -d "$plugin/views" ] && cp -R "$plugin/views" "$stage/sectionist/"
cp "$root/LICENSE" "$stage/sectionist/"

mkdir -p "$root/build"
rm -f "$root/build/sectionist-${version}.zip"
(cd "$stage" && zip -qr "$root/build/sectionist-${version}.zip" sectionist)
echo "Built build/sectionist-${version}.zip"

# Restore the dev autoload (Sectionist\Tests\) that --no-dev removed, so tests keep working.
(cd "$plugin" && composer install --no-interaction --quiet)
