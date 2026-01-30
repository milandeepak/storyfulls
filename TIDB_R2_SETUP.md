# Complete Setup Guide: Free Hosting with TiDB Cloud + Cloudflare R2

This guide will help you deploy Storyfulls on Render's **free plan** using:
- **TiDB Cloud Serverless** (free MySQL-compatible database)
- **Cloudflare R2** (free object storage for files)
- **Render Free Tier** (web hosting)

## Total Monthly Cost: $0

---

## Part 1: Set Up TiDB Cloud Database (5 minutes)

### Step 1: Create TiDB Account

1. Go to **https://tidbcloud.com**
2. Click **Sign Up** (you can use GitHub, Google, or email)
3. Complete the registration

### Step 2: Create Serverless Cluster

1. After logging in, click **Create Cluster**
2. Choose **Serverless** (this is the free tier)
3. Configure:
   - **Cluster Name**: `storyfulls-db`
   - **Region**: Choose closest to your Render region (e.g., `us-west-2` for Oregon)
   - **Cloud Provider**: AWS (recommended)
4. Click **Create**

Wait 2-3 minutes for cluster creation.

### Step 3: Get Connection Details

1. Click on your cluster name
2. Click **Connect** button
3. Choose **MySQL CLI** tab
4. You'll see connection details like:
   ```
   Host: gateway01.us-west-2.prod.aws.tidbcloud.com
   Port: 4000
   User: your_username.root
   ```
5. **SAVE THESE VALUES** - you'll need them for Render

### Step 4: Create Database

1. In TiDB Cloud console, go to **SQL Editor** or use the web console
2. Run this SQL:
   ```sql
   CREATE DATABASE storyfulls CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

### Step 5: Import Your Existing Database (Optional)

If you have an existing MySQL dump:

1. Upload your SQL dump file via TiDB Cloud's import tool, OR
2. Use MySQL CLI:
   ```bash
   mysql -h gateway01.us-west-2.prod.aws.tidbcloud.com \
         -P 4000 \
         -u your_username.root \
         -p \
         --ssl-mode=VERIFY_IDENTITY \
         --ssl-ca=/etc/ssl/cert.pem \
         storyfulls < data/mysql-dump-20260129-030302.sql
   ```

---

## Part 2: Set Up Cloudflare R2 Storage (10 minutes)

### Step 1: Create Cloudflare Account

1. Go to **https://dash.cloudflare.com**
2. Sign up or log in
3. Navigate to **R2** in the sidebar

### Step 2: Subscribe to R2 (Free)

1. Click **Purchase R2 Plan**
2. The free tier includes:
   - 10 GB storage/month
   - 1 million writes/month
   - 10 million reads/month
   - **Unlimited egress** (no bandwidth charges)
3. Complete the checkout (no credit card required for free tier)

### Step 3: Create R2 Bucket

1. Click **Create bucket**
2. Configure:
   - **Bucket name**: `storyfulls-files` (must be globally unique)
   - **Location**: Automatic (or choose region near your users)
3. Click **Create bucket**

### Step 4: Generate API Credentials

1. Go to **R2 > Overview**
2. Click **Manage R2 API Tokens**
3. Click **Create API Token**
4. Configure:
   - **Token Name**: `storyfulls-render`
   - **Permissions**: Object Read & Write
   - **TTL**: Never expire (or set expiration if preferred)
   - **Apply to buckets**: Select `storyfulls-files`
5. Click **Create API Token**
6. **SAVE THESE VALUES** (you won't see them again):
   - **Access Key ID**: `abc123...`
   - **Secret Access Key**: `xyz789...`
   - **S3 Endpoint**: `https://YOUR_ACCOUNT_ID.r2.cloudflarestorage.com`

### Step 5: Enable Public Access

1. Go to your bucket `storyfulls-files`
2. Click **Settings** tab
3. Scroll to **Public access**
4. Click **Allow Access**
5. Copy the **Public Bucket URL**: `https://pub-xxxxx.r2.dev`

**Optional: Use Custom Domain (Recommended)**
1. Click **Connect Domain**
2. Enter subdomain: `files.yourdomain.com`
3. Cloudflare will auto-configure DNS
4. Use this custom domain as your `R2_PUBLIC_URL`

### Step 6: Configure CORS (for Browser Uploads)

1. In bucket settings, scroll to **CORS Policy**
2. Click **Add CORS Policy**
3. Add this configuration:
   ```json
   [
     {
       "AllowedOrigins": ["https://storyfulls.onrender.com", "https://yourdomain.com"],
       "AllowedMethods": ["GET", "PUT", "POST", "DELETE", "HEAD"],
       "AllowedHeaders": ["*"],
       "ExposeHeaders": ["ETag"],
       "MaxAgeSeconds": 3000
     }
   ]
   ```
4. Click **Save**

### Step 7: Upload Existing Files to R2

Use `rclone` to bulk upload your existing files:

#### Install rclone

**Mac:**
```bash
brew install rclone
```

**Linux:**
```bash
curl https://rclone.org/install.sh | sudo bash
```

**Windows:**
Download from https://rclone.org/downloads/

#### Configure rclone for R2

```bash
rclone config

# Follow these prompts:
# n) New remote
# name> r2
# Storage> s3
# provider> Cloudflare
# env_auth> 1 (Enter manually)
# access_key_id> YOUR_R2_ACCESS_KEY_ID
# secret_access_key> YOUR_R2_SECRET_ACCESS_KEY
# region> auto
# endpoint> https://YOUR_ACCOUNT_ID.r2.cloudflarestorage.com
# location_constraint> (leave blank)
# acl> (leave blank)
# Edit advanced config? n
# Remote config> y
```

#### Upload Files

```bash
# From your local development environment
cd /path/to/storyfulls-d11

# Sync all files to R2
rclone sync ./web/sites/default/files/ r2:storyfulls-files/drupal-files/ \
  --progress \
  --transfers 8 \
  --checkers 16 \
  --exclude "sync/**" \
  --exclude "php/**" \
  --exclude "styles/**"

# This will upload:
# - book_covers/ (~600+ book images)
# - hero-images/
# - Any other uploaded content
```

**Note:** We exclude `sync/`, `php/`, and `styles/` because:
- `sync/` - Config exports (not needed in R2)
- `php/` - Temporary files
- `styles/` - Image derivatives (Drupal regenerates these)

---

## Part 3: Deploy to Render (15 minutes)

### Step 1: Push Code to GitHub

```bash
# Commit the changes
git add .
git commit -m "Add TiDB Cloud and Cloudflare R2 support for free hosting"
git push origin main
```

### Step 2: Create Render Account

1. Go to **https://render.com**
2. Sign up with GitHub
3. Authorize Render to access your repositories

### Step 3: Create Web Service

1. Click **New +** > **Web Service**
2. Connect your repository: `storyfulls-d11`
3. Configure:
   - **Name**: `storyfulls`
   - **Region**: Oregon (closest to TiDB us-west-2)
   - **Branch**: `main`
   - **Runtime**: Docker
   - **Plan**: **Free**

### Step 4: Set Environment Variables

Click **Advanced** and add these environment variables:

#### TiDB Database
```
DB_HOST=gateway01.us-west-2.prod.aws.tidbcloud.com
DB_PORT=4000
DB_NAME=storyfulls
DB_USER=your_tidb_username.root
DB_PASS=your_tidb_password
DB_USE_SSL=true
```

#### Drupal Settings
```
DRUPAL_HASH_SALT=<auto-generated>
```

#### Cloudflare R2
```
R2_ACCESS_KEY_ID=your_r2_access_key
R2_SECRET_ACCESS_KEY=your_r2_secret_key
R2_BUCKET=storyfulls-files
R2_ENDPOINT=https://YOUR_ACCOUNT_ID.r2.cloudflarestorage.com
R2_PUBLIC_URL=https://pub-xxxxx.r2.dev
```

### Step 5: Deploy

1. Click **Create Web Service**
2. Wait 5-10 minutes for initial build and deploy
3. Once deployed, note your URL: `https://storyfulls.onrender.com`

### Step 6: Update DRUPAL_BASE_URL

1. Go to **Environment** tab in Render
2. Add:
   ```
   DRUPAL_BASE_URL=https://storyfulls.onrender.com
   ```
3. Click **Save Changes** (this will trigger a redeploy)

---

## Part 4: Configure Drupal for R2 (5 minutes)

### Step 1: Enable S3FS Module

1. Visit `https://storyfulls.onrender.com/user/login`
2. Log in as admin
3. Go to **Extend** (`/admin/modules`)
4. Enable **S3 File System**
5. Click **Install**

### Step 2: Configure S3FS

1. Go to **Configuration > Media > S3 File System** (`/admin/config/media/s3fs`)
2. Verify settings (should auto-populate from environment variables):
   - **Access Key**: ✓ (from R2_ACCESS_KEY_ID)
   - **Secret Key**: ✓ (from R2_SECRET_ACCESS_KEY)
   - **Bucket**: `storyfulls-files`
   - **Region**: `auto`
   - **Use Custom Host**: ✓
   - **Hostname**: Your R2 endpoint
3. Click **Save configuration**

### Step 3: Refresh File Metadata

1. Scroll down to **Actions**
2. Click **Refresh file metadata cache**
3. This will scan your R2 bucket and register all files

### Step 4: Test File Access

1. Go to **Content** and view a book page
2. Check if book cover images load from R2
3. URL should be: `https://pub-xxxxx.r2.dev/drupal-files/book_covers/book_1.jpg`

---

## Part 5: Ongoing Maintenance

### Update Config Sync Storage

Since we no longer have persistent disk for `/sites/default/files/sync`:

#### Option A: Store in Git (Recommended)

```bash
# On local development
ddev drush config:export --destination=../config/sync
git add config/sync/
git commit -m "Export config"
git push
```

Update `settings.render.php`:
```php
$settings['config_sync_directory'] = '../config/sync';
```

#### Option B: Store in R2

Keep using R2, but be aware config imports/exports will be slower.

### Monitor Free Tier Usage

**TiDB Cloud:**
- Dashboard shows: Storage used, Request Units consumed
- Free tier: 5 GB storage, 50M RUs/month

**Cloudflare R2:**
- Dashboard shows: Objects stored, operations count
- Free tier: 10 GB storage, 1M writes, 10M reads/month

**Render:**
- Free tier limitations:
  - Spins down after 15 min of inactivity (cold starts)
  - 750 hours/month (enough for 24/7 uptime)

---

## Troubleshooting

### Database Connection Failed

```bash
# Test TiDB connection locally
mysql -h gateway01.us-west-2.prod.aws.tidbcloud.com \
      -P 4000 \
      -u your_username.root \
      -p \
      --ssl-mode=VERIFY_IDENTITY
```

Check:
- ✓ DB_HOST is correct gateway URL
- ✓ DB_PORT is 4000 (not 3306)
- ✓ DB_USE_SSL is set to "true"
- ✓ Username includes cluster prefix (e.g., `username.root`)

### Images Not Loading from R2

Check:
1. R2 bucket has **public access enabled**
2. Files were uploaded to correct path: `drupal-files/book_covers/`
3. S3FS module is enabled and configured
4. Run `drush s3fs-refresh-cache`

### Site is Slow (Cold Starts)

Render free tier spins down after 15 minutes. First request will be slow.

Solutions:
- Use a cron service to ping your site every 10 minutes (keeps it warm)
- Upgrade to Render Starter plan ($7/mo) for always-on

### Config Import/Export Fails

If using R2 for config sync:
```bash
# Ensure directory exists in R2
drush php-eval "file_prepare_directory('public://sync', FILE_CREATE_DIRECTORY);"

# Or switch to Git-based config (see Part 5)
```

---

## Summary: What You've Achieved

✅ **$0/month hosting** (all free tiers)  
✅ **Scalable storage** (10 GB R2 free, no egress fees)  
✅ **MySQL database** (5 GB TiDB free)  
✅ **No persistent disks needed** (stateless deployment)  
✅ **Production-ready** (SSL, CDN, backups via Cloudflare)

### Estimated Capacity

With free tiers:
- **~600 book cover images** (assuming 200KB avg = 120 MB)
- **~1000 users** (database storage)
- **~50,000 page views/month** (within R2 read limits)

---

## Next Steps

1. Set up custom domain in Render
2. Configure Cloudflare CDN for your domain
3. Set up automated backups:
   - TiDB: Use built-in backup feature
   - R2: Enable versioning for files
4. Monitor usage in dashboards

Need help? Check:
- TiDB Docs: https://docs.pingcap.com/tidbcloud/
- R2 Docs: https://developers.cloudflare.com/r2/
- Render Docs: https://render.com/docs
