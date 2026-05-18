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

## Deploy With GitHub + Render

GitHub Pages cannot run this app because it only hosts static files. This project is prepared for Render as a Docker web service.

1. Push this repository to GitHub.
2. Open Render Dashboard.
3. Choose **New > Blueprint**.
4. Select `https://github.com/eiceejohn00/thesis2.0.git`.
5. Render will read `render.yaml`.
6. Enter an `ADMIN_PASSWORD` when Render asks for the secret value.
7. Deploy.

The app seeds demo audit data from `database/seeders/data/teacher_audit_seed.json` during first startup, so the dashboard will not be blank.

For a simple thesis demo, the included SQLite setup is enough. Edits can reset after redeploys/restarts because the database is created inside the running container. For long-term production use, move the app to a persistent database such as Render Postgres.
