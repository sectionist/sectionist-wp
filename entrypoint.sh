#!/bin/sh
# 1. Abhängigkeiten für Linux prüfen und ggf. installieren
[ -d vendor ] || composer install
[ -d node_modules ] || npm install

# 2. OpenCode starten
exec opencode