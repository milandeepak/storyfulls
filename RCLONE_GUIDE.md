# 🚀 Upload Files to R2 Using Rclone

Follow these steps to upload your existing files to Cloudflare R2.

---

## Step 1: Install Rclone

### macOS
```bash
brew install rclone
```

### Linux
```bash
curl https://rclone.org/install.sh | sudo bash
```

### Windows
Download from: https://rclone.org/downloads/

---

## Step 2: Configure Rclone for R2

Run the setup script I created for you:

```bash
./setup-rclone.sh
```

This will:
- ✅ Check if rclone is installed
- ✅ Create rclone configuration with your R2 credentials
- ✅ Test the connection to your bucket

Expected output:
```
✅ rclone is installed
✅ Rclone configured successfully!
✅ Successfully connected to R2 bucket: storyfulls-files
```

---

## Step 3: Upload Your Files

Run the upload script:

```bash
./upload-to-r2.sh
```

This will:
- 📁 Find all files in `web/sites/default/files/`
- ☁️  Sync them to `r2:storyfulls-files/drupal-files/`
- 🚀 Upload with 8 parallel transfers for speed
- 📊 Show progress in real-time

**Excluded files** (not needed in R2):
- `sync/` - Config exports
- `php/` - Temporary PHP files
- `styles/` - Image derivatives (Drupal regenerates)
- `css/` and `js/` - Aggregated assets

---

## Expected Upload Time

Based on your files:
- ~600 book cover images (~200KB each = ~120 MB)
- Other images and assets

**Estimated time:** 5-10 minutes (depending on internet speed)

---

## Step 4: Verify Upload

After upload completes, verify files are in R2:

```bash
# List all files
rclone ls r2:storyfulls-files/drupal-files

# Check book covers
rclone ls r2:storyfulls-files/drupal-files/book_covers

# Check hero images
rclone ls r2:storyfulls-files/drupal-files/hero-images
```

Or check in Cloudflare Dashboard:
1. Go to https://dash.cloudflare.com
2. Navigate to **R2** → **storyfulls-files**
3. Browse the `drupal-files/` folder

---

## Test File Access

Test if files are publicly accessible:

1. Find a book cover in your local files:
   ```
   web/sites/default/files/book_covers/book_1.jpg
   ```

2. Construct the R2 public URL:
   ```
   https://pub-421b25a0828946dda54e908e094bc6a2.r2.dev/drupal-files/book_covers/book_1.jpg
   ```

3. Open in browser - you should see the image!

---

## Troubleshooting

### "rclone: command not found"
- Install rclone first (see Step 1)

### "Connection test failed"
- Check your internet connection
- Verify R2 credentials are correct
- Make sure bucket "storyfulls-files" exists

### "Permission denied"
- Make scripts executable: `chmod +x setup-rclone.sh upload-to-r2.sh`

### Upload is slow
- This is normal for first upload
- Rclone uses 8 parallel transfers by default
- Subsequent syncs will be much faster (only uploads changes)

---

## What Happens Next?

After files are uploaded to R2, you can:
1. ✅ Deploy to Render (files will be served from R2, not local disk)
2. ✅ Enable S3FS module in Drupal
3. ✅ Drupal will serve images from R2 via your public URL

---

## Manual Rclone Commands (Advanced)

If you want to use rclone manually:

```bash
# List remotes
rclone listremotes

# List buckets
rclone lsd r2:

# List files in bucket
rclone ls r2:storyfulls-files

# Copy a single file
rclone copy ./local-file.jpg r2:storyfulls-files/drupal-files/

# Sync a folder (one-way)
rclone sync ./local-folder r2:storyfulls-files/drupal-files/folder

# Check differences before syncing
rclone check ./web/sites/default/files r2:storyfulls-files/drupal-files

# Delete files in R2 (be careful!)
rclone delete r2:storyfulls-files/drupal-files/old-file.jpg
```

---

## Ready to Deploy?

Once files are uploaded, you're ready for the next step:

**→ Go to NEXT_STEPS.md and continue with STEP 3: Deploy to Render**
