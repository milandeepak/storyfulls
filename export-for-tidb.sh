#!/bin/bash
#
# Database Export Script for TiDB Sync
# Exports DDEV database for import to TiDB Cloud on Render
#

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
BACKUP_DIR="/home/milan/projects"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
BACKUP_FILE="storyfulls-to-tidb-${TIMESTAMP}.sql"
BACKUP_FILE_GZ="${BACKUP_FILE}.gz"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   Storyfulls Database Export for TiDB${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if DDEV is running
if ! ddev status > /dev/null 2>&1; then
    echo -e "${RED}❌ Error: DDEV is not running${NC}"
    echo -e "${YELLOW}💡 Run: ddev start${NC}"
    exit 1
fi

echo -e "${GREEN}✓ DDEV is running${NC}"
echo ""

# Export database
echo -e "${BLUE}📦 Exporting database...${NC}"
ddev drush sql:dump --result-file="/var/www/html/../${BACKUP_FILE}"

if [ ! -f "${BACKUP_DIR}/${BACKUP_FILE}" ]; then
    echo -e "${RED}❌ Error: Database export failed${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Database exported successfully${NC}"
echo ""

# Get file size
FILE_SIZE=$(du -h "${BACKUP_DIR}/${BACKUP_FILE}" | cut -f1)
echo -e "${GREEN}📊 Export size: ${FILE_SIZE}${NC}"
echo ""

# Compress the file
echo -e "${BLUE}🗜️  Compressing database...${NC}"
gzip "${BACKUP_DIR}/${BACKUP_FILE}"
COMPRESSED_SIZE=$(du -h "${BACKUP_DIR}/${BACKUP_FILE_GZ}" | cut -f1)

echo -e "${GREEN}✓ Compressed to: ${COMPRESSED_SIZE}${NC}"
echo ""

# Summary
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}   Export Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📁 File location:${NC}"
echo -e "   ${BACKUP_DIR}/${BACKUP_FILE_GZ}"
echo ""
echo -e "${YELLOW}📋 Next steps:${NC}"
echo -e "   1. Get TiDB credentials from Render Dashboard"
echo -e "   2. Uncompress: ${BLUE}gunzip ${BACKUP_FILE_GZ}${NC}"
echo -e "   3. Import to TiDB using one of these methods:"
echo ""
echo -e "${YELLOW}   Method A - MySQL Command Line:${NC}"
echo -e "   ${BLUE}mysql -h YOUR_TIDB_HOST -P 4000 -u YOUR_USER -p \\${NC}"
echo -e "   ${BLUE}         --ssl-mode=REQUIRED YOUR_DATABASE < ${BACKUP_FILE}${NC}"
echo ""
echo -e "${YELLOW}   Method B - Using Adminer:${NC}"
echo -e "   ${BLUE}1. Deploy Adminer on Render (see SYNC_TO_TIDB.md)${NC}"
echo -e "   ${BLUE}2. Login with TiDB credentials${NC}"
echo -e "   ${BLUE}3. Import ${BACKUP_FILE} via web interface${NC}"
echo ""
echo -e "${YELLOW}📖 Full guide:${NC} SYNC_TO_TIDB.md"
echo ""

# Save import instructions to a file
INSTRUCTIONS_FILE="${BACKUP_DIR}/IMPORT_INSTRUCTIONS_${TIMESTAMP}.txt"
cat > "${INSTRUCTIONS_FILE}" << EOF
TIDB IMPORT INSTRUCTIONS
Generated: ${TIMESTAMP}
Database file: ${BACKUP_FILE_GZ}

STEP 1: Get TiDB Credentials
-----------------------------
Go to Render Dashboard → Your Database Service
Note down:
- Host: ________________
- Port: 4000 (usually)
- Database: ________________
- Username: ________________
- Password: ________________

STEP 2: Uncompress Database
----------------------------
gunzip ${BACKUP_FILE_GZ}

STEP 3: Import to TiDB
----------------------

Option A - Command Line:
mysql -h YOUR_TIDB_HOST \\
      -P 4000 \\
      -u YOUR_USERNAME \\
      -p \\
      --ssl-mode=REQUIRED \\
      YOUR_DATABASE < ${BACKUP_FILE}

Example:
mysql -h gateway01.us-west-2.prod.aws.tidbcloud.com \\
      -P 4000 \\
      -u 3kJxyz123.root \\
      -p \\
      --ssl-mode=REQUIRED \\
      storyfulls_prod < ${BACKUP_FILE}

Option B - Adminer Web Interface:
1. Deploy Adminer to Render (see SYNC_TO_TIDB.md)
2. Access Adminer URL
3. Login with TiDB credentials
4. Click "Import"
5. Upload ${BACKUP_FILE}
6. Execute

STEP 4: Verify
--------------
After import, check:
1. Render Dashboard - Deployment logs
2. Visit site: /young-writers/junior-artists
3. Test 3D carousel functionality
4. Check database tables are populated

IMPORTANT NOTES:
----------------
- Backup production DB before importing!
- Site will be unavailable during import
- Files (images, uploads) are NOT included in DB dump
- Sync sites/default/files separately if needed

EOF

echo -e "${GREEN}✓ Import instructions saved to:${NC}"
echo -e "   ${INSTRUCTIONS_FILE}"
echo ""
