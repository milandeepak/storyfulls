#!/bin/bash

# Rclone Configuration Script for Cloudflare R2
# This script will configure rclone for your R2 bucket

set -e

echo "=========================================="
echo "Rclone Setup for Cloudflare R2"
echo "=========================================="
echo ""

# Check if rclone is installed
if ! command -v rclone &> /dev/null; then
    echo "❌ rclone is not installed."
    echo ""
    echo "Please install rclone first:"
    echo ""
    echo "  macOS:   brew install rclone"
    echo "  Linux:   curl https://rclone.org/install.sh | sudo bash"
    echo "  Windows: Download from https://rclone.org/downloads/"
    echo ""
    exit 1
fi

echo "✅ rclone is installed"
echo ""

# Create rclone config for R2
echo "Creating rclone configuration for Cloudflare R2..."
echo ""

# R2 credentials from your setup
R2_ACCESS_KEY="001aa29ee7f952c236793c752a538935"
R2_SECRET_KEY="8972d2cd2b25f69c66620bea2d4d3f8c6f5c4362cea82367810c435f7321f92e"
R2_ENDPOINT="https://46d8150641bcb7c56022f59981bdf443.r2.cloudflarestorage.com"

# Create rclone config directory if it doesn't exist
mkdir -p ~/.config/rclone

# Create or update rclone config
cat > ~/.config/rclone/rclone.conf << EOF
[r2]
type = s3
provider = Cloudflare
access_key_id = $R2_ACCESS_KEY
secret_access_key = $R2_SECRET_KEY
endpoint = $R2_ENDPOINT
acl = private
no_check_bucket = true
EOF

echo "✅ Rclone configured successfully!"
echo ""
echo "Testing connection to R2..."
echo ""

# Test the connection
if rclone lsd r2:storyfulls-files > /dev/null 2>&1; then
    echo "✅ Successfully connected to R2 bucket: storyfulls-files"
else
    echo "⚠️  Connection test failed. Please check your credentials."
    exit 1
fi

echo ""
echo "=========================================="
echo "Configuration complete!"
echo "=========================================="
echo ""
echo "You can now use rclone with the remote name 'r2'"
echo ""
echo "Example commands:"
echo "  rclone ls r2:storyfulls-files"
echo "  rclone sync ./local-folder r2:storyfulls-files/path"
echo ""
