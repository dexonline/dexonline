#!/usr/bin/env bash
#
# tools/ci/generate-schema.sh
#
# Generates tools/ci/schema.sql — a schema-only dump of the dexonline
# database, committed to the repository and used by the GitLab CI pipeline
# to initialise MySQL without downloading the production database.
#
# The dump is taken from inside the running "db" Docker container
# (tools/docker/docker-compose.yml), so no local MySQL client is required.
#
# Usage (run from anywhere inside the repo):
#   bash tools/ci/generate-schema.sh
#
# Make sure the local Docker stack is up first:
#   cd tools/docker && docker compose up -d
#
# Re-run and recommit schema.sql whenever a new DB patch is applied.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
COMPOSE_FILE="$SCRIPT_DIR/../docker/docker-compose.yml"
OUTPUT="$SCRIPT_DIR/schema.sql"

DB="dexonline"
USER="root"
PASSWORD="admin"   # matches MYSQL_ROOT_PASSWORD in docker-compose.yml

echo "Checking that the db container is running..."
if ! docker compose -f "$COMPOSE_FILE" ps db | grep -q "Up\|running"; then
  echo "ERROR: The 'db' container is not running."
  echo "Start it with:  cd tools/docker && docker compose up -d"
  exit 1
fi

echo "Dumping schema from container db → ${OUTPUT}"

docker compose -f "$COMPOSE_FILE" exec -T db \
  mysqldump \
    -u "$USER" \
    -p"$PASSWORD" \
    --no-data \
    --skip-comments \
    --single-transaction \
    --routines \
    "$DB" \
  | sed 's/AUTO_INCREMENT=[0-9]* //' \
  > "$OUTPUT"

echo "Done. Commit tools/ci/schema.sql to the repository."
