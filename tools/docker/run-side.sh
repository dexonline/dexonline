#!/usr/bin/env bash
set -euo pipefail

BASE_URL=${BASE_URL:-http://localhost:8080/}
SIDE_FILE=${SIDE_FILE:-test/dexonline-test-suite.side}

# Install runner locally in the job context (fast + reproducible)
# We use npx so the workflow doesn’t need to commit node_modules.

echo "Waiting for Selenium WebDriver..."
# Query inside the selenium container so we don't depend on curl being present on the host.
until docker compose -f tools/docker/docker-compose.yml -f tools/docker/docker-compose.ci.yml exec -T selenium bash -lc "curl -fsS http://localhost:4444/wd/hub/status | grep -q '\"ready\":true'"; do
  sleep 1
done

# selenium-side-runner expects a webdriver endpoint; when running from the host,
# it’s localhost:4444 (published from the selenium container).

echo "Running Selenium IDE suite: $SIDE_FILE"
echo "Base URL: $BASE_URL"

npx --yes selenium-side-runner@3.17.0 \
  --base-url "$BASE_URL" \
  --server http://localhost:4444/wd/hub \
  --timeout 60000 \
  --output-directory side-results \
  "$SIDE_FILE"
