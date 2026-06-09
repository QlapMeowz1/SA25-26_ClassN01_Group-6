# BadNet Setup Guide

BadNet is a badminton social network built with Laravel 9, Blade, React, Vite, and MySQL. It includes a player portal, challenges, matches, teams, tournaments, virtual betting, realtime notifications, and an admin panel.

## 1. System Requirements

- Windows 10/11
- PHP 8.1 or newer
- Composer 2
- Node.js 18 or newer and npm
- MySQL 8 or MariaDB through XAMPP
- PHP extensions: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, and `curl`

Verify the required tools:

```powershell
php -v
composer --version
node -v
npm -v
```

When using XAMPP, start MySQL. Apache is optional when the application is served with `php artisan serve`.

## 2. Open the Project Directory

All Laravel and npm commands must run inside the `src` directory:

```powershell
Set-Location "C:\Users\hunte\Desktop\webtest\SA25-26_ClassN01_Group-5\src"
```

Replace the path if the project is stored elsewhere.

## 3. Install Dependencies

```powershell
composer install
npm install
```

## 4. Create the Database

Open phpMyAdmin at `http://localhost/phpmyadmin`, or use the MySQL command line:

```sql
CREATE DATABASE badnet
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

The project currently uses an SQL file for its base schema. Import:

```text
src/database/badnet.sql
```

Using phpMyAdmin:

1. Select the `badnet` database.
2. Open the **Import** tab.
3. Select `src/database/badnet.sql`.
4. Click **Import**.

Do not skip this step. The current migrations upgrade the base schema; they do not create every original table.

## 5. Configure `.env`

Create the environment file if it does not exist:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Minimum local configuration:

```dotenv
APP_NAME=BadNet
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Ho_Chi_Minh
APP_DEMO_DATA=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=badnet
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

`APP_DEMO_DATA` controls UI fallback data:

- `true`: adds demonstration cards when the database contains little data.
- `false`: displays database records only. Use this in production.

Clear cached configuration after changing `.env`:

```powershell
php artisan optimize:clear
```

## 6. Run Migrations

Run these commands after importing `badnet.sql`:

```powershell
php artisan migrate
php artisan migrate:status
```

The migrations add:

- Betting lifecycle and manual odds
- Match result confirmation and disputes
- Notification controls and preferences
- Wallet ledger and audit logs
- Login activity and soft deletion
- Queue tables
- Query indexes

## 7. Build the Frontend

Create a production build:

```powershell
npm run build
```

For frontend development with automatic rebuilding:

```powershell
npm run dev
```

Keep Vite running and use another terminal for Laravel.

## 8. Configure Email

Email is required for:

- Six-digit verification codes after registration
- Forgot-password requests
- Password reset links, which expire after 15 minutes

Example Gmail SMTP configuration:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-account@gmail.com
MAIL_PASSWORD=your-google-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-account@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

For Gmail:

1. Enable two-step verification.
2. Create a Google App Password.
3. Set that App Password as `MAIL_PASSWORD`.
4. Do not use the regular Google account password.
5. Never commit `.env` or share the App Password.

After changing mail settings:

```powershell
php artisan optimize:clear
```

With `QUEUE_CONNECTION=sync`, email is sent immediately and no queue worker is required.

## 9. Create an Administrator

Register an account and verify its email, then open Tinker:

```powershell
php artisan tinker
```

Run:

```php
$user = App\Models\User::where('email', 'your-email@example.com')->firstOrFail();
$user->update(['role' => 'super_admin']);
```

Exit Tinker:

```php
exit
```

Available roles:

- `user`
- `moderator`
- `betting_manager`
- `admin`
- `super_admin`

The admin panel is available at:

```text
http://localhost:8000/admin
```

## 10. Start the Application

```powershell
php artisan serve
```

Open:

```text
http://localhost:8000
```

If port 8000 is occupied:

```powershell
php artisan serve --port=8001
```

## 11. Scheduler

The scheduler sends match reminders one to two hours before a match and removes expired password-reset tokens.

For local development:

```powershell
php artisan schedule:work
```

Inspect scheduled tasks:

```powershell
php artisan schedule:list
```

For Linux production servers, add this cron entry:

```cron
* * * * * cd /path/to/project/src && php artisan schedule:run >> /dev/null 2>&1
```

## 12. Queue

The default local setting is:

```dotenv
QUEUE_CONNECTION=sync
```

This does not require a worker.

To use the database queue:

```dotenv
QUEUE_CONNECTION=database
```

Then run:

```powershell
php artisan optimize:clear
php artisan queue:work --tries=3
```

A queue worker must remain active whenever the queue connection is not `sync`.

## 13. Realtime Notifications and Pool Movement

Without a WebSocket provider, notification data still refreshes through periodic polling. Configure Pusher Channels for immediate realtime updates:

```dotenv
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=ap1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

After changing any `VITE_*` value, rebuild the frontend:

```powershell
php artisan optimize:clear
npm run build
```

Realtime updates are used for:

- Notification red dots and unread counts
- New user notifications
- Betting pool values and distribution percentages

## 14. Demo Data

### UI fallback data

Enable:

```dotenv
APP_DEMO_DATA=true
```

Disable:

```dotenv
APP_DEMO_DATA=false
```

### Database seeder

The seeder creates users, posts, matches, and bets:

```powershell
php artisan db:seed --class=BadmintonSeeder
```

Warning: `BadmintonSeeder` deletes existing `comments` and `posts` before generating new records. Use it only with a local or test database.

The default password for factory-generated users is:

```text
password
```

## 15. Test the Project

Run automated tests:

```powershell
php artisan test
```

Inspect routes, scheduled tasks, and migrations:

```powershell
php artisan route:list
php artisan schedule:list
php artisan migrate:status
```

Verify the production frontend build:

```powershell
npm run build
```

## 16. Daily Local Development Workflow

Terminal 1, Laravel:

```powershell
Set-Location "C:\Users\hunte\Desktop\webtest\SA25-26_ClassN01_Group-5\src"
php artisan serve
```

Terminal 2, scheduler:

```powershell
Set-Location "C:\Users\hunte\Desktop\webtest\SA25-26_ClassN01_Group-5\src"
php artisan schedule:work
```

Terminal 3, only while editing React or JavaScript:

```powershell
Set-Location "C:\Users\hunte\Desktop\webtest\SA25-26_ClassN01_Group-5\src"
npm run dev
```

Terminal 4, only when using the database queue:

```powershell
Set-Location "C:\Users\hunte\Desktop\webtest\SA25-26_ClassN01_Group-5\src"
php artisan queue:work --tries=3
```

## 17. Troubleshooting

### `SQLSTATE[HY000] [1049] Unknown database`

- Create the `badnet` database.
- Verify `DB_DATABASE` in `.env`.

### `Table ... doesn't exist`

- Import `database/badnet.sql`.
- Run `php artisan migrate`.

### `.env` changes have no effect

```powershell
php artisan optimize:clear
```

### Old CSS or JavaScript is still displayed

```powershell
npm run build
php artisan view:clear
```

Then perform a hard refresh with `Ctrl + F5`.

### Email is not sent

- Verify the Gmail App Password.
- Verify `MAIL_FROM_ADDRESS`.
- Run `php artisan optimize:clear`.
- Check `storage/logs/laravel.log`.
- If using a database queue, ensure `php artisan queue:work` is running.

### Realtime notifications do not update

- Verify `BROADCAST_DRIVER=pusher`.
- Verify all `PUSHER_*` values.
- Run `npm run build` again.
- Inspect the Pusher connection in browser developer tools.
- Without Pusher, polling continues to work but is not immediate.

### Admin panel access is denied

- Check the user's role through Tinker.
- The role must be `admin`, `super_admin`, `moderator`, or `betting_manager`.
- Each role only sees the management sections allowed by its capabilities.

### Route, view, or configuration cache errors

```powershell
php artisan optimize:clear
php artisan view:cache
php artisan route:cache
php artisan config:cache
```

## 18. Production Configuration

Recommended values:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_DEMO_DATA=false
QUEUE_CONNECTION=database
BROADCAST_DRIVER=pusher
```

Basic deployment commands:

```powershell
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
```

Production also requires:

- HTTPS
- A queue worker managed by Supervisor or systemd
- A cron job for the Laravel scheduler
- Regular database backups
- Separate SMTP and Pusher credentials
- Write permissions for `storage` and `bootstrap/cache`

Never commit `.env`, Gmail App Passwords, database passwords, or Pusher secrets.
