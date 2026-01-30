# 🚀 QUICK START GUIDE - Next Steps

Your credentials are ready! Follow these steps in order:

---

## ✅ STEP 1: Enable R2 Public Access (2 minutes)

You need to get the public URL for your R2 bucket.

1. Go to **Cloudflare Dashboard**: https://dash.cloudflare.com
2. Navigate to **R2** in the sidebar
3. Click on your bucket: **storyfulls-files**
4. Click the **Settings** tab
5. Scroll to **Public access** section
6. Click **Allow Access** button
7. **COPY the Public Bucket URL** that appears (looks like `https://pub-xxxxx.r2.dev`)
8. Update `.env.render` file with this URL (I'll help you with this next)

---

## ✅ STEP 2: Upload Your Files to R2 (10-15 minutes)

Before deploying, let's upload your existing files to R2.

### Option A: Using Cloudflare Dashboard (Easier, but slower)

1. Go to your bucket **storyfulls-files**
2. Click **Upload** button
3. Create folder structure: `drupal-files/book_covers/`
4. Drag and drop your book images from `web/sites/default/files/book_covers/`

### Option B: Using rclone (Faster for bulk upload)

I can help you set this up. First, install rclone:

**On Mac:**
```bash
brew install rclone
```

**On Linux:**
```bash
curl https://rclone.org/install.sh | sudo bash
```

Then we'll configure it with your R2 credentials.

**Which option do you prefer?** (A or B)

---

## ✅ STEP 3: Create Render Account & Deploy (5 minutes)

1. Go to **https://render.com**
2. Click **Get Started** and sign up with GitHub
3. Authorize Render to access your repositories
4. Click **New +** → **Web Service**
5. Find and select your repository: **storyfulls-d11**
6. Configure:
   - **Name**: `storyfulls`
   - **Region**: `Singapore` (closest to your TiDB database)
   - **Branch**: `main`
   - **Runtime**: Docker
   - **Instance Type**: **Free**
7. Click **Advanced** to expand environment variables section

---

## ✅ STEP 4: Set Environment Variables in Render

Copy these values from your `.env.render` file into Render's Environment section:

### Database (TiDB)
```
DB_HOST = gateway01.ap-southeast-1.prod.aws.tidbcloud.com
DB_PORT = 4000
DB_NAME = test
DB_USER = 3vrSKi82ZrYmHeMroot
DB_PASS = cYgl765CUuQBshcM
DB_USE_SSL = true
```

### R2 Storage
```
R2_ACCESS_KEY_ID = 001aa29ee7f952c236793c752a538935
R2_SECRET_ACCESS_KEY = 8972d2cd2b25f69c66620bea2d4d3f8c6f5c4362cea82367810c435f7321f92e
R2_ENDPOINT = https://46d8150641bcb7c56022f59981bdf443.r2.cloudflarestorage.com
R2_BUCKET = storyfulls-files
R2_PUBLIC_URL = [PASTE YOUR PUBLIC URL FROM STEP 1]
```

### Drupal
```
DRUPAL_HASH_SALT = (leave empty - Render will auto-generate)
```

Click **Create Web Service**

---

## ✅ STEP 5: Wait for Deployment (10-15 minutes)

Render will:
1. Clone your repo
2. Build Docker image
3. Deploy the container
4. Run database migrations

Watch the logs in Render dashboard. You'll see:
- ✓ Building Dockerfile
- ✓ Installing dependencies
- ✓ Starting PHP-FPM and nginx
- ✓ Running drush deploy

---

## ✅ STEP 6: Import Your Database (5 minutes)

Once deployed, you need to import your existing database to TiDB.

### Using MySQL CLI:

```bash
# From your local machine
mysql -h gateway01.ap-southeast-1.prod.aws.tidbcloud.com \
      -P 4000 \
      -u 3vrSKi82ZrYmHeMroot \
      -p \
      --ssl-mode=VERIFY_IDENTITY \
      --ssl-ca=/etc/ssl/cert.pem \
      test < data/mysql-dump-20260129-030302.sql
```

When prompted, enter password: `cYgl765CUuQBshcM`

---

## ✅ STEP 7: Enable S3FS Module (3 minutes)

1. Visit your new site: `https://storyfulls.onrender.com`
2. Log in as admin: `/user/login`
3. Go to **Extend**: `/admin/modules`
4. Find **S3 File System** and enable it
5. Click **Install**
6. Go to **Configuration** → **Media** → **S3 File System**: `/admin/config/media/s3fs`
7. Verify settings are populated from environment variables
8. Click **Refresh file metadata cache**

---

## ✅ STEP 8: Update Base URL (2 minutes)

1. Go back to Render dashboard
2. Go to **Environment** tab
3. Add new variable:
   ```
   DRUPAL_BASE_URL = https://storyfulls.onrender.com
   ```
4. Click **Save Changes** (this triggers a redeploy)

---

## 🎉 DONE!

Your site should now be fully working on:
- ✅ **Free hosting** on Render
- ✅ **Free database** on TiDB Cloud (5GB)
- ✅ **Free storage** on Cloudflare R2 (10GB)

**Total cost: $0/month**

---

## 📊 Monitor Your Usage

### TiDB Cloud
- Dashboard: https://tidbcloud.com
- Check: Storage usage, Request Units

### Cloudflare R2
- Dashboard: https://dash.cloudflare.com → R2
- Check: Objects count, Operations

### Render
- Dashboard: https://dashboard.render.com
- Check: Deploy logs, Service health

---

## ⚠️ IMPORTANT NOTES

1. **First request will be slow** - Render free tier has "cold starts" after 15 min inactivity
2. **Database name is "test"** - You may want to rename this in TiDB dashboard to "storyfulls"
3. **Backup regularly** - Export your database periodically
4. **Monitor free tier limits** - You have plenty of room for a small-medium site

---

## 🆘 Need Help?

If anything goes wrong, check:
1. Render deployment logs for errors
2. TiDB connection (test with MySQL CLI)
3. R2 public access is enabled
4. All environment variables are set correctly

Let me know which step you're on and I can help!
