#!/usr/bin/env bash
#
# tools/ci/run.sh
#
# Runs the Selenium test suite locally using Docker Compose, mimicking the
# GitHub Actions pipeline step-for-step.
#
# Usage:
#   bash tools/ci/run.sh
#
# Requirements:
#   - Docker + Docker Compose
#   - tools/ci/schema.sql committed (generate with tools/ci/generate-schema.sh)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
COMPOSE="docker compose -f $SCRIPT_DIR/docker-compose.yml"

# Convenience wrapper: run a command inside the app container
app() { $COMPOSE exec -T app "$@"; }

cd "$ROOT_DIR"

echo "==> Building images and starting services..."
$COMPOSE build
$COMPOSE up -d --wait   # waits for all healthchecks to pass

echo "==> Setting Romanian collation on main database..."
$COMPOSE exec -T db mysql -u root -pci_root \
  -e "ALTER DATABASE dexonline CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci;"

echo "==> Importing schema..."
$COMPOSE exec -T db mysql -u root -pci_root dexonline \
  < "$SCRIPT_DIR/schema.sql"

echo "==> Configuring application..."
app cp Config.php.sample Config.php
app cp www/.htaccess.sample www/.htaccess
app bash -c "sed -i \
  -e \"s|const DATABASE = .*;|const DATABASE = 'mysql://root:ci_root@db/dexonline';|\" \
  -e \"s|const TEST_DATABASE = .*;|const TEST_DATABASE = 'mysql://root:ci_root@db/dexonline_test';|\" \
  -e \"s|const URL_HOST = .*;|const URL_HOST = 'http://localhost';|\" \
  -e \"s|const URL_PREFIX = .*;|const URL_PREFIX = '/dexonline/www/';|\" \
  -e \"s|const DEVELOPMENT_MODE = .*;|const DEVELOPMENT_MODE = true;|\" \
  -e \"s|const TEST_MODE = .*;|const TEST_MODE = true;|\" \
  Config.php"

echo "==> Setting permissions..."
app chmod 777 templates_c www/css/merged www/js/merged www/img/generated
app bash -c "touch /var/log/dexonline.log && chmod 666 /var/log/dexonline.log"

echo "==> Compiling Levenshtein binary..."
app gcc -O2 -Wall -o lib/c/levenshtein lib/c/levenshtein.c

echo "==> Seeding test database..."
app php tools/resetTestingDatabase.php

echo "==> Running Selenium tests..."
# selenium-side-runner talks to the Selenium container via its service name.
# The app is reachable from inside the app container on localhost:80.
set +e
app selenium-side-runner \
  --server "http://selenium:4444/wd/hub" \
  --base-url "http://localhost/dexonline/www/" \
  --timeout 30000 \
  --output-directory test/reports \
  test/dexonline-test-suite.side
EXIT_CODE=$?
set -e

echo ""
if [ $EXIT_CODE -eq 0 ]; then
  echo "==> All tests passed."
else
  echo "==> Tests FAILED (exit code $EXIT_CODE)."
  echo "    Apache error log:"
  app bash -c "cat /var/log/dex-error.log 2>/dev/null || true"
  echo "    Application log:"
  app bash -c "cat /var/log/dexonline.log 2>/dev/null || true"
fi

echo "==> Stopping services..."
$COMPOSE down

exit $EXIT_CODE
