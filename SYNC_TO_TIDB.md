# Database Sync to TiDB Cloud (Render Production)

## Current Status
✅ Code changes pushed to GitHub (Young Artists page + Community Picks updates)
⚠️ Database changes are only in local DDEV environment
🎯 **Goal:** Sync local database to TiDB Cloud on Render.com

---

## Option 1: Direct Database Import (Recommended for Major Changes)

### Step 1: Export Local Database
```bash
# From your project root
ddev drush sql:dump --gzip --result-file=../storyfulls-production-$(date +%Y%m%d).sql

# This creates: storyfulls-production-YYYYMMDD.sql.gz
# Location: /home/milan/projects/storyfulls-production-YYYYMMDD.sql.gz
```

### Step 2: Get TiDB Connection Details from Render

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Find your database service (should be TiDB Cloud or MySQL)
3. Click on it to view connection details
4. Note down:
   - **Host:** (e.g., `gateway01.us-west-2.prod.aws.tidbcloud.com`)
   - **Port:** (usually `4000` for TiDB)
   - **Database:** (your database name)
   - **Username:** (your TiDB username)
   - **Password:** (your TiDB password)

### Step 3: Import to TiDB Using MySQL Client

```bash
# Uncompress the SQL dump
gunzip storyfulls-production-YYYYMMDD.sql.gz

# Import to TiDB (replace with your actual credentials)
mysql -h YOUR_TIDB_HOST \
      -P 4000 \
      -u YOUR_USERNAME \
      -p \
      --ssl-mode=REQUIRED \
      YOUR_DATABASE_NAME < storyfulls-production-YYYYMMDD.sql
```

**Example:**
```bash
mysql -h gateway01.us-west-2.prod.aws.tidbcloud.com \
      -P 4000 \
      -u 3kJxyz123.root \
      -p \
      --ssl-mode=REQUIRED \
      storyfulls_prod < storyfulls-production-20260201.sql
```

---

## Option 2: Use Adminer (Easiest for Small Databases)

### Step 1: Deploy Adminer on Render
Follow: [Deploy Adminer on Render](https://render.com/docs/deploy-adminer)

Or use this quick Blueprint:
```yaml
services:
  - type: web
    name: adminer-temp
    env: docker
    dockerfilePath: Dockerfile
    dockerContext: .
```

Create `Dockerfile` for Adminer:
```dockerfile
FROM adminer:latest
```

### Step 2: Access Adminer
1. Once deployed, open the Adminer URL (e.g., `https://adminer-temp.onrender.com`)
2. Login with your TiDB credentials:
   - **System:** MySQL
   - **Server:** `YOUR_TIDB_HOST:4000`
   - **Username:** Your TiDB username
   - **Password:** Your TiDB password
   - **Database:** Your database name

### Step 3: Import Database
1. Click **Import** in Adminer
2. Upload your `.sql` file (uncompressed)
3. Click **Execute**
4. Wait for import to complete

---

## Option 3: Configuration-Based Sync (For Schema Changes Only)

If you only want to sync configuration (not content like books, users), use Drupal's config export/import:

### Step 1: Export Configuration
```bash
# Create a config directory that's version controlled
mkdir -p config/sync
ddev drush config:set system.file config_sync_directory ../config/sync -y
ddev drush config:export -y
```

### Step 2: Commit Configuration
```bash
git add config/
git commit -m "Export Drupal configuration"
git push
```

### Step 3: Import on Production
When Render redeploys, add this to `render-start.sh`:
```bash
./vendor/bin/drush config:import -y
```

**⚠️ Note:** This only syncs configuration (fields, content types, views), NOT content (books, users, etc.)

---

## Option 4: Automated Script (For Regular Syncs)

Create a sync script:

```bash
#!/bin/bash
# sync-to-tidb.sh

set -e

echo "🔄 Syncing local database to TiDB Cloud..."

# Configuration
TIDB_HOST="${TIDB_HOST:-gateway01.us-west-2.prod.aws.tidbcloud.com}"
TIDB_PORT="${TIDB_PORT:-4000}"
TIDB_USER="${TIDB_USER}"
TIDB_PASS="${TIDB_PASS}"
TIDB_DATABASE="${TIDB_DATABASE:-storyfulls}"
BACKUP_FILE="backup-$(date +%Y%m%d-%H%M%S).sql"

# Step 1: Export local database
echo "📦 Exporting local database..."
ddev drush sql:dump --result-file="/var/www/html/${BACKUP_FILE}"

# Step 2: Import to TiDB
echo "📤 Importing to TiDB Cloud..."
mysql -h "$TIDB_HOST" \
      -P "$TIDB_PORT" \
      -u "$TIDB_USER" \
      -p"$TIDB_PASS" \
      --ssl-mode=REQUIRED \
      "$TIDB_DATABASE" < "$BACKUP_FILE"

echo "✅ Database sync complete!"
echo "📋 Backup saved: $BACKUP_FILE"
```

Usage:
```bash
chmod +x sync-to-tidb.sh

export TIDB_HOST="your-tidb-host"
export TIDB_USER="your-username"
export TIDB_PASS="your-password"
export TIDB_DATABASE="storyfulls"

./sync-to-tidb.sh
```

---

## Important Notes

### ⚠️ Before Importing to Production

1. **Backup Production First:**
   ```bash
   # SSH into Render or use Adminer to export production DB
   mysqldump -h TIDB_HOST -P 4000 -u USER -p DATABASE > production-backup.sql
   ```

2. **Test in Staging:** If you have a staging environment, test there first

3. **Downtime:** During import, the site may be unavailable

4. **File Uploads:** Database import doesn't include files. Ensure `sites/default/files` is synced separately

### 🔐 Security

- Never commit TiDB credentials to Git
- Use environment variables for credentials
- Rotate passwords regularly
- Delete temporary Adminer after use

### 📊 What Gets Synced

**Database Import syncs:**
- ✅ Content (books, users, nodes, etc.)
- ✅ Configuration (content types, fields, views)
- ✅ Taxonomy terms
- ✅ User accounts and permissions
- ✅ Flags (wishlist, read books, likes)

**Database Import does NOT sync:**
- ❌ Uploaded files (must sync `sites/default/files` separately)
- ❌ Custom code (already in Git)
- ❌ Theme files (already in Git)

---

## Recommended Workflow

**For this update (Young Artists page):**

1. ✅ Code already pushed to GitHub
2. 🔄 Export local database (Step 1 above)
3. 📤 Import to TiDB using Adminer or mysql command (easiest)
4. 🚀 Render will auto-deploy the code changes
5. ✨ Both code and database will be in sync

**For future updates:**

- Small changes (config only): Use Option 3 (config export/import)
- Content changes: Use Option 1 (full database import)
- Regular syncs: Set up Option 4 (automated script)

---

## Quick Start (What to Do Now)

```bash
# 1. Export your local database
cd /home/milan/projects/storyfulls-d11
ddev drush sql:dump --gzip --result-file=../storyfulls-backup-$(date +%Y%m%d).sql

# 2. The file is now at: /home/milan/projects/storyfulls-backup-YYYYMMDD.sql.gz

# 3. Get your TiDB credentials from Render Dashboard

# 4. Import using one of the methods above (Adminer recommended for ease)
```

---

Need help with any step? Let me know which option you'd like to proceed with!
