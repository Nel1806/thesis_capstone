# Teacher Audit System

Laravel-based teacher audit dashboard for SDO Marikina City. It includes login, school audit summaries, editable school-level audit rows, and editable class organization parameters.

## Local Setup

### XAMPP (MySQL / MariaDB)

This copy is configured for PHP + MySQL on XAMPP. Start **Apache** and **MySQL** in the XAMPP Control Panel first.

1. Create the database (phpMyAdmin or command line):

   ```sql
   SOURCE database/setup_xampp.sql;
   ```

   Or import `database/setup_xampp.sql` in phpMyAdmin.

2. Install dependencies and initialize the app:

   ```bash
   composer install
   copy .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   php artisan audit:sync-catalog
   ```

3. Open the app:

   - **Artisan server:** `php artisan serve` then visit http://127.0.0.1:8000
   - **Apache:** http://localhost/thesis_capstone/public

   If using Apache, set `APP_URL=http://localhost/thesis_capstone/public` in `.env`.

Default `.env` database settings:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thesis_capstone
DB_USERNAME=root
DB_PASSWORD=
```

### SQLite (optional)

To use SQLite instead of MySQL, set `DB_CONNECTION=sqlite` in `.env`, create an empty `database/database.sqlite` file, then run `php artisan migrate --seed`.

### Generic Laravel setup

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

Recommended Railway variables (web service; link MySQL plugin or paste credentials):

```text
APP_NAME=Teacher Audit System
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stderr
APP_URL=https://your-app.up.railway.app
APP_KEY=base64:from-php-artisan-key-generate-show

DB_CONNECTION=mysql
DB_HOST=mysql.railway.internal
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-mysql-password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
ADMIN_EMAIL=admin@deped.gov.ph
ADMIN_PASSWORD=choose-a-password
```

The Docker image includes `pdo_mysql`. On startup, `docker/start.sh` runs migrations, seeds, and `audit:sync-catalog`.

For SQLite instead of MySQL, set `DB_CONNECTION=sqlite` and omit the MySQL `DB_*` variables.
