# Importing your existing Drupal MySQL DB into Render (Option 1)

This project’s Render setup uses a **MySQL private service** (`storyfulls-mysql`). To avoid a “fresh install”, you import your existing DDEV/MySQL database into that Render MySQL service.

## 1) Export from DDEV

From your local machine:

```bash
cd /home/milan/projects/storyfulls-d11
ddev start
ddev export-db --gzip=false --file=/tmp/storyfulls.sql
```

## 2) Import into Render MySQL (recommended: Adminer)

Render private MySQL isn’t directly reachable from the public internet. The easiest import method is **Adminer** (web UI) in the same Render workspace.

1. Deploy Adminer following Render’s guide:
   `https://render.com/docs/deploy-adminer`
2. Open your Adminer URL.
3. Log in using:
   - **System**: MySQL
   - **Server**: `<your-mysql-service-name>:3306` (e.g. `storyfulls-mysql:3306`)
   - **Username**: `storyfulls` (or your configured `MYSQL_USER`)
   - **Password**: the `MYSQL_PASSWORD` you set when creating the Blueprint
   - **Database**: `storyfulls` (or your configured `MYSQL_DATABASE`)
4. Use Adminer’s **Import** to upload `/tmp/storyfulls.sql`.

## 3) Copy uploaded files

Database import does not include uploaded files. Copy your local folder:

`web/sites/default/files`

into the Render web service disk mounted at:

`/var/www/html/web/sites/default/files`

(You can do this via Render shell by downloading a zip and extracting it into that path.)

## 4) Restart / clear caches

After DB + files are in place, redeploy the web service (or run `drush cr`) so Drupal picks up everything cleanly.

