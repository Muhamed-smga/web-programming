# Sarajevo2B PHP API

This PHP API uses FlightPHP, PDO, and MySQL. It is separate from the existing Node agent scaffold in `server.js`.

## Database and JWT configuration

1. Import the project schema into MySQL:

   ```powershell
   cmd.exe /c "C:\xampp\mysql\bin\mysql.exe -u root -p < ..\Database\db.sql"
   ```

2. Ensure `config/config.php` exists locally. It is ignored by Git. In a fresh checkout, copy the template and replace its database values and JWT secret:

   ```powershell
   Copy-Item config\config.example.php config\config.php
   ```

   For the environment-backed local config, set these variables before starting the API:

   ```powershell
   $env:SARAJEVO2B_DB_HOST = "127.0.0.1"
   $env:SARAJEVO2B_DB_PORT = "3306"
   $env:SARAJEVO2B_DB_NAME = "sarajevo2b"
   $env:SARAJEVO2B_DB_USER = "root"
   $env:SARAJEVO2B_DB_PASSWORD = ""
   $env:SARAJEVO2B_JWT_SECRET = "<a random secret with at least 32 characters>"
   ```

   The environment-backed local `config/config.php` reads these values and provides XAMPP defaults for the database name, host, port, and local `root` account. Configure a non-empty DB password where your MySQL installation requires one. If you copied the template, edit those values directly in the ignored local file. The API rejects the template JWT placeholder because it is shorter than 32 characters. Do not commit `config/config.php` or real secrets.

## Install and run

From `Backend/`, install the declared PHP dependencies, then run the API with PHP's development server:

```powershell
composer install
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t . index.php
```

Composer must be installed, and PHP needs the `zip` extension or a system unzip/7-Zip tool to extract package archives. The API is available at `http://127.0.0.1:8000/api`. For XAMPP Apache, enable `mod_rewrite` and configure the virtual host document root to `Backend/`; the included `.htaccess` forwards API paths to the FlightPHP front controller. Login with `POST /api/auth/login`; send its JWT on protected routes using the standard Bearer authorization scheme. User roles are loaded from MySQL for each authenticated request. Public registration always creates a regular user.

Public read endpoints are `GET /api/places`, `/api/categories`, `/api/events`, and `/api/reviews`, including their `/{id}` forms. Admins manage places, categories, and events. Authenticated users may create reviews and edit/delete their own; admins may moderate any review. Only admins manage users, while users may view/update their own profile through `/api/me` or `/api/users/{id}`.

The database schema currently contains no `favorites` table, so this API does not expose a Favorites DAO or endpoints. No schema changes are made.

## Test

Run the standalone DAO/service test suite (requires PHP with PDO SQLite enabled):

```powershell
C:\xampp\php\php.exe tests\dao_test.php
```

The test uses an in-memory schema and exercises CRUD, partial updates, input/column validation, password hashing, SQL-parameter handling, and foreign-key enforcement without modifying the project database.
