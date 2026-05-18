# Teacher Audit System

Laravel-based teacher audit dashboard for SDO Marikina City. It includes login, school audit summaries, editable school-level audit rows, and editable class organization parameters.

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Default demo login:

```text
admin@deped.gov.ph
password
```

## Deploy With GitHub + Railway

GitHub Pages cannot run this app because it only hosts static files. This project is prepared for Railway as a Docker web service.

1. Push this repository to GitHub.
2. Open Railway.
3. Create a **New Project**.
4. Choose **Deploy from GitHub repo**.
5. Select `eiceejohn/thesis2.0`.
6. Add the variables below.
7. Deploy.
8. In the service **Settings > Networking**, click **Generate Domain**.

Recommended Railway variables:

```text
APP_NAME=Teacher Audit System
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stderr
DB_CONNECTION=sqlite
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
ADMIN_EMAIL=admin@deped.gov.ph
ADMIN_PASSWORD=choose-a-password
APP_KEY=base64:generate-this-with-php-artisan-key-generate-show
```

The app seeds demo audit data from `database/seeders/data/teacher_audit_seed.json` during first startup, so the dashboard will not be blank.

For a simple thesis demo, the included SQLite setup is enough. Edits can reset after redeploys/restarts unless you attach a Railway volume and let the app use `RAILWAY_VOLUME_MOUNT_PATH` for the SQLite database.
