# Deploying Storyfulls (Drupal 11) on Render

This project can be hosted on [Render](https://render.com) so you can share a live URL with clients without running a local server or ngrok.

## What you get

- **Web service**: Drupal 11 behind nginx + PHP-FPM, built from the repo with Docker.
- **PostgreSQL database**: Managed by Render (free tier available).
- **Persistent disk**: For `sites/default/files` (uploads and config sync).

**Important:** The app uses **PostgreSQL** on Render. If your local site uses MySQL (e.g. with DDEV), you’ll need to either run a fresh Drupal install on Render or migrate your data to PostgreSQL before/after deploy.

---

## Option A: Deploy with Blueprint (recommended)

1. **Push this repo to GitHub** (or GitLab) and ensure `Dockerfile`, `render.yaml`, `render-start.sh`, and `web/sites/default/settings.render.php` are committed.

2. **Go to [Render Dashboard](https://dashboard.render.com)** → **New** → **Blueprint**.

3. **Connect the repo** that contains this project and select it. Render will read `render.yaml` and create:
   - A PostgreSQL database (`storyfulls-db`)
   - A web service (`storyfulls`) using the Dockerfile

4. **Create the Blueprint.** When prompted for any secret values (e.g. `DRUPAL_HASH_SALT`), you can let Render generate them.

5. **Wait for the first deploy.** The build runs `composer install` and generates `settings.php` from `default.settings.php` + Render-specific settings when `DATABASE_URL` is set.

6. **Install Drupal or restore data**
   - **Fresh install:** Open `https://<your-service-name>.onrender.com/core/install.php` and complete the installer (database is already configured).
   - **Existing DB:** Export your current DB, convert/import into the Render Postgres instance, then run `drush cr` (e.g. via a one-off job or SSH if available).

7. **Optional:** In the web service **Environment** tab, add:
   - `DRUPAL_BASE_URL` = `https://<your-service-name>.onrender.com`  
   so that links and file URLs use HTTPS correctly.

---

## Option B: Manual setup (without Blueprint)

1. In Render, create a **PostgreSQL** database (e.g. `storyfulls-db`), same region as the web service.

2. Create a **Web Service**:
   - Connect the repo.
   - **Runtime:** Docker.
   - **Build:** Uses repo root `Dockerfile` (no extra build command).
   - **Start:** Uses `CMD` from Dockerfile (no extra start command).

3. In the web service **Environment**:
   - `DATABASE_URL` = **Internal Database URL** from the Postgres service (from the database’s “Connect” / “Internal” URL).
   - `DRUPAL_HASH_SALT` = generate one, e.g. `openssl rand -hex 32`.

4. Under **Advanced** → **Disks**, add a disk:
   - **Mount Path:** `/var/www/html/web/sites/default/files`
   - **Size:** e.g. 1 GB.

5. Deploy. Then install Drupal at `/core/install.php` or import your database as in Option A.

---

## Files used for Render

| File | Purpose |
|------|--------|
| `Dockerfile` | nginx + PHP-FPM, document root = `web/` |
| `render-start.sh` | Generates `settings.php`, runs `composer install`, ensures files dir, then starts the server |
| `web/sites/default/settings.render.php` | Reads `DATABASE_URL` and sets database, trusted host, hash salt, etc. |
| `render.yaml` | Blueprint: web service + Postgres + disk + env vars |
| `.dockerignore` | Keeps image smaller by excluding dev/generated content |

---

## Free tier notes

- **Web service:** Spins down after inactivity; first request after idle may be slow (cold start).
- **PostgreSQL:** Free plan has limits (e.g. 1 GB, 90-day expiry on free tier in some regions). Check [Render pricing](https://render.com/pricing).
- **Disk:** Free tier may have size limits; 1 GB is usually enough for a demo.

---

## Troubleshooting

- **White screen / 500:** Check **Logs** in the Render dashboard. Ensure `DATABASE_URL` is set and is the **internal** connection string.
- **“Trusted host” error:** Add your Render URL to trusted hosts; `settings.render.php` already allows `*.onrender.com`. If you use a custom domain, set `TRUSTED_HOST_PATTERNS` (comma-separated) or add it in `settings.render.php`.
- **Composer / Drush errors in logs:** The start script runs `composer install` and optionally `drush deploy`. If the DB isn’t ready yet, Drush may fail once; the site can still work after you run the installer or import the DB.

Once the service is live, share the Render URL (e.g. `https://storyfulls.onrender.com`) with your client.
