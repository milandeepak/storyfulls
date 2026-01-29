# Migrating Drupal data from DDEV (MySQL) to Render (PostgreSQL)

Use these scripts to copy your local Drupal database to Render’s PostgreSQL so the live site has the same content, users, and config.

---

## 1. Export a backup (optional)

Creates a MySQL dump in `data/` for backup or inspection. **Not required** for the migration (the migration script talks to DDEV directly).

```bash
ddev start
./scripts/export-mysql-ddev.sh
```

Dump path: `data/mysql-dump-YYYYMMDD-HHMMSS.sql` (folder `data/` is in `.gitignore`).

---

## 2. Migrate MySQL → PostgreSQL (Render)

Uses **pgloader** in Docker to copy from DDEV’s MySQL to Render’s PostgreSQL.

### Prerequisites

- **DDEV** running (`ddev start`)
- **Docker** (for pgloader)
- **Render** PostgreSQL created (e.g. via Blueprint) and its **Internal Database URL**

### Steps

1. **Get the Internal Database URL from Render**
   - Dashboard → your **PostgreSQL** service (e.g. `storyfulls-db`)
   - **Connect** (or **Info**) → copy the **Internal** connection string  
   - It looks like:  
     `postgresql://user:password@dpg-xxxxx-a.oregon-postgres.render.com:5432/dbname`  
   - If you only see `postgres://`, use `postgresql://` instead (same URL, just change the scheme).

2. **Set the URL and run the migration**
   ```bash
   ddev start
   export RENDER_DATABASE_URL='postgresql://USER:PASSWORD@HOST:5432/DATABASE'
   chmod +x scripts/migrate-mysql-to-postgres-render.sh
   ./scripts/migrate-mysql-to-postgres-render.sh
   ```
   Replace `USER`, `PASSWORD`, `HOST`, and `DATABASE` with the values from the Internal URL (or paste the full URL in one go).

3. **Wait for pgloader to finish**  
   It will create tables, copy data, and fix sequences. Large DBs can take a few minutes.

4. **Clear Drupal cache on Render**  
   After the first request, the Render start script may run `drush deploy` / `drush cr`.  
   Or trigger a deploy from the Render dashboard so the app restarts and picks up the new data.

---

## 3. Sync files (uploads) to Render

The database migration does **not** copy `sites/default/files` (uploaded images, etc.). Options:

- **Manual:** Zip `web/sites/default/files` and upload/extract via Render Shell (if available) into `/var/www/html/web/sites/default/files` (same path as the disk mount).
- **rsync / S3:** If you add an S3 (or similar) bucket later, you could use Drupal’s S3 module and sync files there; for a simple demo, copying the most important files manually is often enough.

---

## Troubleshooting

- **“Could not detect DDEV MySQL host port”**  
  Run `ddev describe` and ensure the `db` service is running. The script parses the MySQL host port from `ddev describe` (or `ddev describe -j` if `jq` is installed).

- **pgloader: connection refused to host.docker.internal**  
  On Linux, Docker must support `host-gateway`. Upgrade Docker or run pgloader without Docker (install pgloader locally and point it at `127.0.0.1:PORT` for MySQL and your Render Postgres URL).

- **Render: white screen or DB errors after migration**  
  Ensure the **Internal** Database URL is used (not the external one) and that the web service has `DATABASE_URL` set. Redeploy the web service after the migration so it uses the new data.

- **Passwords with special characters in RENDER_DATABASE_URL**  
  URL-encode them (e.g. `@` → `%40`, `#` → `%23`). Or set the URL in Render’s Environment and run the migration from a machine that can reach both DDEV and Render (e.g. your laptop with DDEV and the URL in the environment).

---

## Summary

| Step | Command / action |
|------|-------------------|
| Backup (optional) | `./scripts/export-mysql-ddev.sh` |
| Migrate to Render Postgres | `export RENDER_DATABASE_URL='...'` then `./scripts/migrate-mysql-to-postgres-render.sh` |
| Clear cache on Render | Redeploy or run `drush cr` via Shell |
| Files | Copy `web/sites/default/files` to Render disk if needed |
