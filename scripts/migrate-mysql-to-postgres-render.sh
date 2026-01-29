#!/usr/bin/env bash
# Migrate Drupal database from DDEV (MySQL/MariaDB) to Render's PostgreSQL
# using pgloader in Docker. Run with DDEV started and RENDER_DATABASE_URL set.
#
# Usage:
#   1. In Render Dashboard: Web Service -> Environment -> get "Internal Database URL"
#      from the linked PostgreSQL service (Internal connection string).
#   2. export RENDER_DATABASE_URL='postgresql://user:pass@host:5432/dbname'
#      (use the Internal URL; if it says postgres://, change to postgresql://)
#   3. ddev start
#   4. ./scripts/migrate-mysql-to-postgres-render.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

cd "$PROJECT_ROOT"

if [ -z "$RENDER_DATABASE_URL" ]; then
  echo "Error: RENDER_DATABASE_URL is not set."
  echo ""
  echo "Get the Internal Database URL from Render:"
  echo "  Dashboard -> your PostgreSQL database -> Connect -> Internal connection string"
  echo "Then run:"
  echo "  export RENDER_DATABASE_URL='postgresql://user:password@host:5432/database'"
  echo "  $0"
  exit 1
fi

# Normalize scheme to postgresql:// for pgloader
RENDER_DATABASE_URL="${RENDER_DATABASE_URL/postgres:\/\//postgresql:\/\/}"

if ! command -v ddev &>/dev/null; then
  echo "Error: ddev not found."
  exit 1
fi

if ! ddev describe &>/dev/null; then
  echo "Error: DDEV not running. Run: ddev start"
  exit 1
fi

# Get MySQL port on host (DDEV exposes db:3306 to a random host port)
MYSQL_PORT=""
if command -v jq &>/dev/null; then
  MYSQL_PORT=$(ddev describe -j 2>/dev/null | jq -r '.raw.dbinfo.published_port // empty')
fi
if [ -z "$MYSQL_PORT" ]; then
  MYSQL_PORT=$(ddev describe 2>/dev/null | grep -oP 'db:3306\s*->\s*127\.0\.0\.1:\K[0-9]+' | head -1)
fi
if [ -z "$MYSQL_PORT" ]; then
  echo "Error: Could not detect DDEV MySQL host port. Run: ddev describe"
  exit 1
fi

# pgloader: connect from Docker to host's MySQL via host.docker.internal
# On Linux we need --add-host=host.docker.internal:host-gateway
MYSQL_URL="mysql://db:db@host.docker.internal:${MYSQL_PORT}/db"

LOAD_FILE=$(mktemp --suffix=.load 2>/dev/null || echo /tmp/migrate-render.load)
trap "rm -f '$LOAD_FILE'" EXIT

cat > "$LOAD_FILE" << LOADEOF
LOAD DATABASE
     FROM $MYSQL_URL
     INTO $RENDER_DATABASE_URL
WITH include drop, create tables, create indexes, reset sequences
SET work_mem to '256MB', maintenance_work_mem to '512MB';
LOADEOF

echo "Migrating MySQL (DDEV port $MYSQL_PORT) -> Render PostgreSQL..."
echo "Using pgloader (Docker). This may take a few minutes."
echo ""

docker run --rm \
  --add-host=host.docker.internal:host-gateway \
  -v "$LOAD_FILE:/migrate.load:ro" \
  dimitri/pgloader:latest \
  pgloader /migrate.load

echo ""
echo "Migration finished. Clear Drupal cache on Render (e.g. Drush one-off or visit /admin/config/development/performance and clear cache)."
