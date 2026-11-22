# Sarajevo2B PHP API

This PHP API uses FlightPHP, PDO, and MySQL. It is separate from the existing Node agent scaffold in `server.js`.

## Quick start (Windows/XAMPP, PowerShell)

1. In the XAMPP Control Panel, start **MySQL**. From `Backend/`, import the schema (this creates the local `sarajevo2b` database and its tables):

   ```powershell
   cmd.exe /c '"C:\xampp\mysql\bin\mysql.exe" -u root -p < "..\Database\db.sql"'
   ```

   Omit `-p` if the local MySQL `root` account has no password. Ensure `config/config.php` exists; in a fresh checkout, copy the ignored local config template:

   ```powershell
   Copy-Item config\config.example.php config\config.php
   ```

2. Set the required environment variables in the same PowerShell window that will run the server. Generate a local JWT secret of at least 32 characters; provide your own OpenWeatherMap key. Set the CORS origins to the exact frontend origin(s), including port when present:

   ```powershell
   $secretBytes = New-Object byte[] 32
   $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
   $rng.GetBytes($secretBytes)
   $env:SARAJEVO2B_JWT_SECRET = [Convert]::ToBase64String($secretBytes)
   $rng.Dispose()
   $env:SARAJEVO2B_WEATHER_API_KEY = "<OpenWeatherMap API key>"
   $env:SARAJEVO2B_CORS_ORIGINS = "http://localhost,http://127.0.0.1"
   ```

   For a frontend served on another port, include its full origin, for example `http://localhost:5500`. The local config defaults database host/port/name/user to the XAMPP values and reads `SARAJEVO2B_DB_PASSWORD` when set. Do not commit `config/config.php` or real secrets.

3. From `Backend/`, install dependencies and start the API:

   ```powershell
   composer install
   C:\xampp\php\php.exe -S 127.0.0.1:8000 -t . index.php
   ```

   The API is available at `http://127.0.0.1:8000/api`. For XAMPP Apache, enable `mod_rewrite` and configure the virtual host document root to `Backend/`; the included `.htaccess` forwards API paths to the FlightPHP front controller.

4. Open Swagger at `http://127.0.0.1:8000/api/docs`. Log in with either development account listed below. Swagger UI and its CSS/JavaScript load from unpkg.com, so `/api/docs` requires internet access.

5. In another PowerShell window opened in `Backend/`, run the suites:

   ```powershell
   C:\xampp\php\php.exe tests\dao_test.php
   C:\xampp\php\php.exe tests\external_api_service_test.php
   C:\xampp\php\php.exe tests\api_smoke_test.php
   C:\xampp\php\php.exe tests\openapi_test.php
   ```

   The smoke and OpenAPI tests require a running API server. Run the smoke test against a disposable database seeded from `Database/db.sql`, not a database containing real data. Its default API URL is `http://127.0.0.1:8000/api`; override it with `SARAJEVO2B_API_BASE_URL` when needed.

Composer must be installed, and PHP needs the `zip` extension or a system unzip/7-Zip tool to extract package archives. Login with `POST /api/auth/login`; send its JWT on protected routes using the standard Bearer authorization scheme. User roles are loaded from MySQL for each authenticated request. Public registration always creates a regular user.

Public read endpoints are `GET /api/places`, `/api/categories`, `/api/events`, and `/api/reviews`, including their `/{id}` forms. Admins manage places, categories, and events. Authenticated users may create reviews and edit/delete their own; admins may moderate any review. Only admins manage users, while users may view/update their own profile through `/api/me` or `/api/users/{id}`.

`GET /api/external/weather` returns current weather for a place (`?place_id=...`), coordinates (`?lat=...&lon=...`), or Sarajevo by default. Configure `SARAJEVO2B_WEATHER_API_KEY` to enable it. Provider non-2xx responses and invalid or empty provider data return HTTP 502; network failures and timeouts return 504. An unknown local `place_id` returns 404, and a missing API key returns 500. Provider details and the API key are not included in client error responses.

## Swagger UI

Start the API server as shown above, then open `http://127.0.0.1:8000/api/docs`. Swagger UI loads the generated OpenAPI document from `GET /api/docs/openapi.json` and requires internet access for its unpkg.com assets. To try protected operations, log in through the Swagger UI `POST /api/auth/login` operation, copy the returned token, select **Authorize**, enter the token (without the `Bearer ` prefix), and confirm. Swagger UI applies the bearer scheme to protected operations.

Validation failures and business-rule violations return HTTP 400. Event end times must be later than start times, and event/review place references must exist. A user cannot repeat a review, delete a category used by places, change their own role, or delete the last administrator. Successful deletes return HTTP 200 with `{"data":{"message":"<Entity> deleted successfully."}}`.

The SQL seed includes two **development-only** accounts: `admin@sarajevo2b.com` / `Admin123!` (admin) and `user@sarajevo2b.com` / `User123!` (regular user). Do not use these credentials outside local development.

Configure `SARAJEVO2B_CORS_ORIGINS` as a comma-separated list of exact frontend origins (scheme, hostname, and port when applicable). The local default is `http://localhost,http://127.0.0.1`; never use a wildcard origin with Authorization headers.

The database schema currently contains no `favorites` table, so this API does not expose a Favorites DAO or endpoints. No schema changes are made.

## Test

Run the standalone DAO/service test suite (requires PHP with PDO SQLite enabled):

```powershell
C:\xampp\php\php.exe tests\dao_test.php
C:\xampp\php\php.exe tests\external_api_service_test.php
C:\xampp\php\php.exe tests\api_smoke_test.php
C:\xampp\php\php.exe tests\openapi_test.php
```

The DAO test uses an in-memory schema and exercises CRUD, partial updates, input/column validation, password hashing, SQL-parameter handling, and foreign-key enforcement without modifying the project database. Start the API server first, then run the smoke test against a disposable seeded database. Its default base URL is `http://127.0.0.1:8000/api`; override it with `SARAJEVO2B_API_BASE_URL` when needed. The smoke test uses uniquely named records and deletes the records it creates.
The OpenAPI test also requires the server to be running. It fetches the generated specification, validates OpenAPI 3.x and internal references, and checks documented operations against route registrations in `index.php`.
