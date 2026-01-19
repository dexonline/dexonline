#!/usr/bin/env bash
set -euo pipefail

# Wait for MySQL to accept connections
until docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T db mysql -uroot -padmin -e 'SELECT 1' >/dev/null 2>&1; do
  echo "Waiting for MySQL..."
  sleep 1
done

echo "Starting application setup..."
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash tools/setup.sh

# Match tools/docker/run.sh behavior as closely as possible

docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -c "chmod 666 /var/www/html/Config.php" || true
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -c "touch /var/log/dexonline.log"
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -c "chmod 666 /var/log/dexonline.log" || true

# Set DB location + URL prefix (same quoting style as run.sh)
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -c "sed -i 's|const DATABASE = .*;|const DATABASE = '\''mysql://root:admin@db.localhost/dexonline'\'';|' Config.php"
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -c "sed -i 's|const URL_PREFIX = .*;|const URL_PREFIX = '\''/'\'';|' Config.php"

# Extra CI-only config for test DB
# Note: resetTestingDatabase.php uses both TEST_DATABASE (PDO) and mysqldump/mysql shell commands.
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -c "sed -i 's|const TEST_DATABASE = .*;|const TEST_DATABASE = '\''mysql://root:admin@db.localhost/dexonline_test'\'';|' Config.php"
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -c "sed -i 's|const TEST_MODE = .*;|const TEST_MODE = true;|' Config.php"
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -c "sed -i 's|const DEVELOPMENT_MODE = .*;|const DEVELOPMENT_MODE = true;|' Config.php"

# Provide MySQL CLI credentials for tools/resetTestingDatabase.php.
# That script refuses to include passwords in CLI flags and requires ~/.my.cnf.
# In CI containers, $HOME can differ (root vs www-data), so write it to $HOME plus common homes.
# Also run a quick auth smoke test so we fail early (instead of "using password: NO").
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -lc '
  set -euo pipefail

  echo "app container user: $(id -u):$(id -g) ($(id -un))"
  echo "HOME=$HOME"

  write_mycnf() {
    local home="$1"
    mkdir -p "$home"
    cat > "$home/.my.cnf" <<EOF
[client]
user=root
password=admin
host=db.localhost
EOF
    chmod 600 "$home/.my.cnf"
  }

  for home in "$HOME" /root /var/www /var/www/html; do
    write_mycnf "$home"
  done

  echo "Wrote MySQL client config to: $HOME, /root, /var/www, /var/www/html"
  echo "MySQL auth smoke test (should not require -p)..."
  mysql -h db.localhost -u root -e "SELECT 1" >/dev/null
  echo "MySQL auth OK via option file."
'

# Fail fast if the config didn't actually update (this is what causes 'using password: NO')
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -lc "\
  grep -q \"const DATABASE = 'mysql://root:admin@db.localhost/dexonline';\" Config.php && \
  grep -q \"const TEST_DATABASE = 'mysql://root:admin@db.localhost/dexonline_test';\" Config.php && \
  grep -q \"const TEST_MODE = true;\" Config.php && \
  grep -q \"const DEVELOPMENT_MODE = true;\" Config.php\
" || {
  echo "Config.php didn't get the expected CI settings. Dumping relevant lines:";
  docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -lc "grep -n \"const DATABASE\|const TEST_DATABASE\|const TEST_MODE\|const DEVELOPMENT_MODE\|const URL_PREFIX\" Config.php || true";
  exit 1;
}

echo "Initializing dexonline schema by importing dex-database.sql.gz (source of truth)..."

docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T db mysql -uroot -padmin -e "CREATE DATABASE IF NOT EXISTS dexonline CHARACTER SET utf8mb4 COLLATE utf8mb4_romanian_ci;"

docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T db bash -lc "set -e; \
  wget -qO /tmp/dex-database.sql.gz https://dexonline.ro/static/download/dex-database.sql.gz; \
  gzip -d -f /tmp/dex-database.sql.gz; \
  pv /tmp/dex-database.sql | MYSQL_PWD=admin mysql -uroot dexonline"

echo "Resetting test DB (dexonline_test)..."
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app php tools/resetTestingDatabase.php

# Basic HTTP sanity check (run from inside the app container to avoid needing curl on the runner)
docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T app bash -lc "\
  until wget -qO- http://localhost/ >/dev/null 2>&1; do \
    echo 'Waiting for HTTP (app)...'; \
    sleep 1; \
  done\
"
