#!/usr/bin/env bash
set -euo pipefail

# Wait for MySQL to accept connections
until docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T db mysql -uroot -padmin -e 'SELECT 1' >/dev/null 2>&1; do
  echo "Waiting for MySQL..."
  sleep 1
done

# Configure app (mirror tools/docker/run.sh, plus test DB settings)
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash tools/setup.sh

# Make Config.php writable in container (it’s bind-mounted)
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -lc 'chmod 666 /var/www/html/Config.php || true'

docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -lc "touch /var/log/dexonline.log && chmod 666 /var/log/dexonline.log"

# Point the app to the DB container and set URL prefix for Apache docroot
# Also enable test/development modes and set TEST_DATABASE.
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -lc "\
  sed -i \"s|const DATABASE = .*;|const DATABASE = 'mysql://root:admin@db.localhost/dexonline';|\" Config.php && \
  sed -i \"s|const TEST_DATABASE = .*;|const TEST_DATABASE = 'mysql://root:admin@db.localhost/dexonline_test';|\" Config.php && \
  sed -i \"s|const URL_PREFIX = .*;|const URL_PREFIX = '/';|\" Config.php && \
  sed -i \"s|const TEST_MODE = .*;|const TEST_MODE = true;|\" Config.php && \
  sed -i \"s|const DEVELOPMENT_MODE = .*;|const DEVELOPMENT_MODE = true;|\" Config.php\
"

# Create a minimal “production” DB schema and a deterministic test DB.
# resetTestingDatabase.php copies schema from DATABASE -> TEST_DATABASE, so DATABASE must exist and have schema.
# We create schema by applying patches/*.sql (best-effort) without downloading the huge prod dump.

echo "Initializing dexonline schema by importing dex-database.sql.gz (source of truth)..."

# Create main DB

docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T db mysql -uroot -padmin -e "CREATE DATABASE IF NOT EXISTS dexonline CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci;"

# Import full schema+data (this can take a while)
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T db bash -lc "set -e; \
  wget -qO /tmp/dex-database.sql.gz https://dexonline.ro/static/download/dex-database.sql.gz; \
  gzip -d -f /tmp/dex-database.sql.gz; \
  pv /tmp/dex-database.sql | MYSQL_PWD=admin mysql -uroot dexonline"

# Now build a deterministic test DB used by the Selenium suite.
echo "Resetting test DB (dexonline_test)..."
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app php tools/resetTestingDatabase.php

# Basic HTTP sanity check (run from inside the app container to avoid needing curl on the runner)
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -lc "\
  until wget -qO- http://localhost/ >/dev/null 2>&1; do \
    echo 'Waiting for HTTP (app)...'; \
    sleep 1; \
  done\
"
