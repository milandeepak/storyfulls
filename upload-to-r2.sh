#!/bin/bash

# Upload Drupal Files to Cloudflare R2
# This script syncs your local files to R2 storage

set -e

echo "=========================================="
echo "Upload Files to Cloudflare R2"
echo "=========================================="
echo ""

# Check if rclone is configured
if ! rclone listremotes | grep -q "r2:"; then
    echo "❌ rclone remote 'r2' not found."
    echo ""
    echo "Please run ./setup-rclone.sh first to configure rclone."
    echo ""
    exit 1
fi

# Define paths
LOCAL_FILES="./web/sites/default/files"
R2_BUCKET="r2:storyfulls-files"
R2_PATH="drupal-files"

echo "📁 Local path: $LOCAL_FILES"
echo "☁️  R2 bucket: $R2_BUCKET/$R2_PATH"
echo ""

# Check if local files directory exists
if [ ! -d "$LOCAL_FILES" ]; then
    echo "❌ Local files directory not found: $LOCAL_FILES"
    echo ""
    echo "Are you running this from the project root?"
    exit 1
fi

# Count files to upload
FILE_COUNT=$(find "$LOCAL_FILES" -type f | wc -l | tr -d ' ')
echo "Found $FILE_COUNT files to sync"
echo ""

# Ask for confirmation
read -p "Do you want to proceed with the upload? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Upload cancelled."
    exit 0
fi

echo ""
echo "Starting upload..."
echo "This may take several minutes depending on file size and internet speed."
echo ""

# Sync files to R2
# Excludes:
# - sync/ (config sync, not needed in R2)
# - php/ (temporary PHP files)
# - styles/ (image derivatives, Drupal regenerates these)
# - css/ and js/ (aggregated assets, regenerated)

rclone sync "$LOCAL_FILES" "$R2_BUCKET/$R2_PATH" \
    --progress \
    --transfers 8 \
    --checkers 16 \
    --exclude "sync/**" \
    --exclude "php/**" \
    --exclude "styles/**" \
    --exclude "css/**" \
    --exclude "js/**" \
    --exclude ".htaccess" \
    --stats 10s \
    --stats-one-line

echo ""
echo "=========================================="
echo "✅ Upload Complete!"
echo "=========================================="
echo ""

# Show uploaded files summary
echo "Files in R2 bucket:"
rclone ls "$R2_BUCKET/$R2_PATH" --max-depth 2 | head -20

echo ""
echo "To view all files:"
echo "  rclone ls $R2_BUCKET/$R2_PATH"
echo ""
echo "To check specific folders:"
echo "  rclone ls $R2_BUCKET/$R2_PATH/book_covers"
echo "  rclone ls $R2_BUCKET/$R2_PATH/hero-images"
echo ""
