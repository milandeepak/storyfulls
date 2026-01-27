# Banner Management System - Setup Instructions

## What's Been Created

A complete banner management system that allows you to manage page banners from the Drupal admin interface.

## Installation Steps

1. **Enable the module** (run from the project root):
   ```bash
   drush en storyfulls_banners -y
   drush cr
   ```

2. **Clear cache**:
   ```bash
   drush cr
   ```

## How to Use

### Accessing Banner Management

1. Log in to the Drupal admin interface
2. Navigate to **Content** → **Manage Banners** (`/admin/content/banners`)
3. You'll see a list of all configured banners

### Adding a New Banner

1. Click **"Add Banner"** button
2. Fill in the form:
   - **Banner Name**: A descriptive name (e.g., "Events Page Banner")
   - **Machine Name**: Auto-generated from the banner name
   - **Page Type**: Select which page this banner is for:
     - Events Page
     - Blog Page
     - Books Page
   - **Banner Image**: Upload your banner image
     - Recommended size: 1920x400 pixels
     - Supported formats: PNG, JPG, JPEG, GIF, WebP
     - Max file size: 10MB
   - **Alt Text**: Accessibility text for the image
3. Click **"Save"**

### Editing a Banner

1. Go to **Content** → **Manage Banners**
2. Click **"Edit"** next to the banner you want to modify
3. Make your changes
4. Click **"Save"**

### Deleting a Banner

1. Go to **Content** → **Manage Banners**
2. Click **"Delete"** next to the banner
3. Confirm deletion

## Important Notes

- **One banner per page type**: You can only have one active banner for each page (Events, Blog, or Books)
- **Fallback images**: If no banner is configured, the system will use the original hardcoded images
- **Styles preserved**: All existing CSS classes and page layouts remain unchanged
- **File management**: Uploaded images are stored in `public://banners/` directory

## Page Types

- **Events Page**: Banner for `/events` page
- **Blog Page**: Banner for `/blogs` page
- **Books Page**: Banner for the books listing page (node/33)

## Permissions

The "Administer page banners" permission is required to manage banners. Make sure your admin role has this permission.

## File Locations

Created files:
- Module: `/web/modules/custom/storyfulls_banners/`
- Banner images: Uploaded to `public://banners/` (typically `/web/sites/default/files/banners/`)

## Troubleshooting

**Q: Changes don't appear on the site**  
A: Clear Drupal cache: `drush cr`

**Q: Can't upload images**  
A: Check that the `public://banners/` directory is writable

**Q: Banner doesn't show after upload**  
A: Ensure you've cleared the cache and the image file uploaded successfully
