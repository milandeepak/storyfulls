# ⚠️ IMPORTANT: Run These Scripts On Your Local Machine

Since rclone requires installation and I don't have sudo access on this server, you'll need to run the upload scripts from **your local computer** (not on the server).

---

## 🖥️ On Your Local Machine

### Step 1: Make sure you have the latest code

```bash
# Pull the latest changes (if you're working from a different machine)
git pull origin main
```

### Step 2: Install rclone

**macOS:**
```bash
brew install rclone
```

**Linux:**
```bash
curl https://rclone.org/install.sh | sudo bash
```

**Windows:**
- Download from: https://rclone.org/downloads/
- Or use Chocolatey: `choco install rclone`

### Step 3: Run the setup script

```bash
cd /path/to/storyfulls-d11
./setup-rclone.sh
```

Expected output:
```
==========================================
Rclone Setup for Cloudflare R2
==========================================

✅ rclone is installed
Creating rclone configuration for Cloudflare R2...
✅ Rclone configured successfully!
Testing connection to R2...
✅ Successfully connected to R2 bucket: storyfulls-files

==========================================
Configuration complete!
==========================================
```

### Step 4: Upload files to R2

```bash
./upload-to-r2.sh
```

You'll see:
```
==========================================
Upload Files to Cloudflare R2
==========================================

📁 Local path: ./web/sites/default/files
☁️  R2 bucket: r2:storyfulls-files/drupal-files

Found 627 files to sync

Do you want to proceed with the upload? (y/n)
```

Type `y` and press Enter.

The upload will show progress:
```
Starting upload...
Transferred:      120.5 MiB / 120.5 MiB, 100%, 2.5 MiB/s, ETA 0s
Transferred:      627 / 627, 100%
Elapsed time:     48.2s
```

### Step 5: Verify upload

Test in browser:
```
https://pub-421b25a0828946dda54e908e094bc6a2.r2.dev/drupal-files/book_covers/book_1.jpg
```

---

## Alternative: Manual Upload via Cloudflare Dashboard

If you can't install rclone, you can upload manually:

1. Go to https://dash.cloudflare.com
2. Navigate to **R2** → **storyfulls-files**
3. Create folder: `drupal-files`
4. Inside that, create: `book_covers`
5. Upload files from `web/sites/default/files/book_covers/`
6. Repeat for other folders like `hero-images`, etc.

**Note:** This is slower but works for smaller numbers of files.

---

## What Files Need to Be Uploaded?

From your local `web/sites/default/files/` folder, upload:

- ✅ `book_covers/` (~600 book images) - **IMPORTANT**
- ✅ `hero-images/` (banner backgrounds)
- ✅ `book-covers/` (alternative covers)
- ✅ Any dated folders like `2026-01/`
- ❌ Skip `sync/` (config files, not needed)
- ❌ Skip `php/` (temporary files)
- ❌ Skip `styles/` (Drupal regenerates these)

---

## After Upload is Complete

Once files are in R2, come back and let me know. Then we'll proceed to:

1. ✅ Push code to GitHub
2. ✅ Deploy to Render
3. ✅ Import database to TiDB
4. ✅ Configure Drupal

---

## Need Help?

Let me know if you:
- ❌ Can't install rclone
- ❌ Get any errors during upload
- ❌ Prefer manual upload via dashboard
- ✅ Successfully uploaded and ready for next step
