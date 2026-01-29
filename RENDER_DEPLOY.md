# Deploying Storyfulls (Drupal 11) on Render

This project can be hosted on [Render](https://render.com) so you can share a live URL with clients without running a local server or ngrok.

## What you get

- **Web service**: Drupal 11 behind nginx + PHP-FPM, built from the repo with Docker.
- **MySQL 8 database**: A Render **private service** (internal-only) backed by a persistent disk.
- **Persistent disk**: For `sites/default/files` (uploads and generated files).

**Important:** This setup uses **MySQL** on Render so you can import your existing DDEV/MySQL database without converting to PostgreSQL.

---

## Option A: Deploy with Blueprint (recommended)

1. **Push this repo to GitHub** (or GitLab) and ensure `Dockerfile`, `render.yaml`, `render-start.sh`, and `web/sites/default/settings.render.php` are committed.

2. **Go to [Render Dashboard](https://dashboard.render.com)** → **New** → **Blueprint**.

3. **Connect the repo** that contains this project and select it. Render will read `render.yaml` and create:
   - A private MySQL service (`storyfulls-mysql`)
   - A web service (`storyfulls`) using the Dockerfile

4. **Create the Blueprint.** You will be prompted for `MYSQL_PASSWORD` (this becomes the DB password used by Drupal). Save it somewhere.

5. **Wait for the first deploy.** The container generates `settings.php` and starts nginx/PHP-FPM. Drupal will connect to MySQL using `DB_HOST/DB_NAME/DB_USER/DB_PASS`.

6. **Import your existing database (no fresh install)**
   - Export from DDEV (example): `ddev export-db --gzip=false --file=/tmp/storyfulls.sql`
   - Import into Render MySQL:
     - Easiest: deploy **Adminer** on Render and use its **Import** UI to upload the SQL dump.
       See [Deploy Adminer on Render](https://render.com/docs/deploy-adminer).
     - Alternative: use the Render shell/SSH on a service in the same workspace and run `mysql` to import.

7. **Optional:** In the web service **Environment** tab, add:
   - `DRUPAL_BASE_URL` = `https://<your-service-name>.onrender.com`  
   so that links and file URLs use HTTPS correctly.

8. **Copy uploaded files (`sites/default/files`)**
   - Your DB import does not include uploaded files.
   - Attachments/uploads must be copied into the web service disk at:
     - `/var/www/html/web/sites/default/files`
   - Quick approach: zip your local folder and make it temporarily downloadable, then `curl` it from the Render shell and unzip into the path above.

---

## Option B: Manual setup (without Blueprint)

1. In Render, create a **private MySQL service** (MySQL 8) with a disk mounted at `/var/lib/mysql`.
   See [Deploy MySQL](https://render.com/docs/deploy-mysql).

2. Create a **Web Service**:
   - Connect the repo.
   - **Runtime:** Docker.
   - **Build:** Uses repo root `Dockerfile` (no extra build command).
   - **Start:** Uses `CMD` from Dockerfile (no extra start command).

3. In the web service **Environment**:
   - `DB_HOST` = the MySQL private service host (e.g. `storyfulls-mysql`)
   - `DB_PORT` = `3306`
   - `DB_NAME`, `DB_USER`, `DB_PASS` = match MySQL env vars
   - `DRUPAL_HASH_SALT` = generate one, e.g. `openssl rand -hex 32`

4. Under **Advanced** → **Disks**, add a disk:
   - **Mount Path:** `/var/www/html/web/sites/default/files`
   - **Size:** e.g. 5 GB (adjust as needed).

5. Deploy. Then import your MySQL dump (Adminer recommended) and copy `sites/default/files`.

---

## Files used for Render

| File | Purpose |
|------|--------|
| `Dockerfile` | nginx + PHP-FPM, document root = `web/` |
| `render-start.sh` | Generates `settings.php`, ensures files dir, starts PHP-FPM + nginx |
| `web/sites/default/settings.render.php` | Reads `DB_HOST/DB_NAME/DB_USER/DB_PASS` (MySQL) or `DATABASE_URL` (fallback) |
| `render.yaml` | Blueprint: web service + MySQL private service + disks + env vars |
| `.dockerignore` | Keeps image smaller by excluding dev/generated content |

---

## Free tier notes

- **Web service:** Spins down after inactivity; first request after idle may be slow (cold start).
- **PostgreSQL:** Free plan has limits (e.g. 1 GB, 90-day expiry on free tier in some regions). Check [Render pricing](https://render.com/pricing).
- **Disk:** Free tier may have size limits; 1 GB is usually enough for a demo.

---

## Troubleshooting

- **White screen / 500:** Check **Logs** in the Render dashboard. Ensure `DB_HOST/DB_NAME/DB_USER/DB_PASS` are set and match the MySQL service.
- **“Trusted host” error:** Add your Render URL to trusted hosts; `settings.render.php` already allows `*.onrender.com`. If you use a custom domain, set `TRUSTED_HOST_PATTERNS` (comma-separated) or add it in `settings.render.php`.
- **DB import:** If you can’t SSH into services, deploy Adminer and import the SQL dump via the browser.

Once the service is live, share the Render URL (e.g. `https://storyfulls.onrender.com`) with your client.
