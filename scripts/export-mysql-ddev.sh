#!/usr/bin/env bash
# Export MySQL database from DDEV to a file (backup + for inspection).
# Does NOT convert to PostgreSQL; use migrate-mysql-to-postgres-render.sh for that.

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
OUTPUT_DIR="${PROJECT_ROOT}/data"
DUMP_FILE="${OUTPUT_DIR}/mysql-dump-$(date +%Y%m%d-%H%M%S).sql"

cd "$PROJECT_ROOT"

if ! command -v ddev &>/dev/null; then
  echo "Error: ddev not found. Run this from a machine with DDEV."
  exit 1
fi

if ! ddev describe &>/dev/null; then
  echo "Error: DDEV project not running. Run: ddev start"
  exit 1
fi

mkdir -p "$OUTPUT_DIR"
echo "Exporting MySQL from DDEV to ${DUMP_FILE} ..."
ddev export-db --gzip=false --file="${OUTPUT_DIR}/mysql-dump.sql"
mv "${OUTPUT_DIR}/mysql-dump.sql" "$DUMP_FILE" 2>/dev/null || true

echo "Done. Dump saved to: ${DUMP_FILE}"
echo "To migrate this data to Render's PostgreSQL, use: scripts/migrate-mysql-to-postgres-render.sh"
