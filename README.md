# BadNet

BadNet is a badminton-focused social platform that combines community features, match organization, tournaments, virtual pool betting, player progression, and operational administration in one application.

The main application uses Laravel Blade and vanilla CSS. A React player portal is also bundled through Vite. MySQL stores the application data, while Laravel Echo and Pusher can provide realtime notification and betting-pool updates.

## Core Features

### Player Community

- Registration with six-digit email verification
- Login activity tracking and account security controls
- Password recovery with 15-minute reset tokens
- Player profiles with badminton attributes, rankings, wallet data, and achievements
- Community posts, images, likes, comments, and replies
- Light and dark themes
- English and Vietnamese interface support

### Challenges and Matches

- Create direct or open challenges
- Accept or reject invitations and join requests
- Find opponents based on player information
- Create and schedule badminton matches
- Prevent conflicting player and court schedules
- Submit match results for opponent confirmation
- Dispute results for administrator review
- ELO rating updates after confirmed results
- Match reminders through the Laravel scheduler

### Teams and Tournaments

- Create, join, and leave teams
- View team members and team information
- Leader and administrator member management
- Create and register for tournaments
- Tournament participant management
- Registration status, capacity, prize pools, and event progress

### Virtual Betting

- Pool-based virtual betting on eligible matches
- Dynamic pool distribution and odds
- Manual odds management by match creators and authorized administrators
- Wallet balance validation before placing a bet
- Atomic stake deduction and wallet ledger entries
- Idempotent payout settlement
- Full refunds for cancelled markets
- Betting, wallet, result, and odds-change notifications
- Realtime pool movement through Laravel broadcasting

### Notification Center

- Interaction, match, betting/wallet, and system categories
- Unread badge counter
- Mark one or all notifications as read
- Mark notifications as unread
- Pin and delete notifications
- Deep links to related posts, matches, bets, teams, or tournaments
- User notification preferences
- Realtime updates with polling fallback

### Admin Panel

- Dashboard, players, tournaments, schedule, court bookings, betting, moderation, statistics, and audit logs
- Role-based access for moderators, betting managers, administrators, and super administrators
- Add, ban, unban, soft-delete, restore, and bulk-manage users
- Assign roles, including administrator roles
- Adjust user wallet balances with transaction and audit records
- Moderate posts and comments
- Review and resolve disputed match results
- Manage betting markets, odds, suspensions, and refunds
- Export player data as CSV

## Technology Stack

### Backend

- PHP 8.1+
- Laravel 9
- Laravel Blade
- Eloquent ORM
- MySQL or MariaDB
- Laravel Notifications, Scheduler, Queue, and Broadcasting

### Frontend

- Vanilla CSS for the main Laravel interface
- React 18 for the player portal
- TypeScript
- Vite
- Tailwind CSS in the React bundle
- Laravel Echo and Pusher JS
- Lucide React icons

### Testing

- PHPUnit
- SQLite in-memory database for automated tests

## Project Structure

```text
.
|-- README.md
|-- SETUP.md
`-- src/
    |-- app/
    |   |-- Events/                 Realtime broadcast events
    |   |-- Http/Controllers/       User and admin request handlers
    |   |-- Http/Middleware/        Authentication and access control
    |   |-- Models/                 Eloquent models
    |   |-- Notifications/          Email notifications
    |   `-- Services/               Betting, wallet, ELO, and audit logic
    |-- config/                     Laravel configuration
    |-- database/
    |   |-- badnet.sql              Base MySQL schema
    |   |-- migrations/             Schema upgrades
    |   `-- seeders/                Optional demonstration data
    |-- public/
    |   |-- build/                  Vite production assets
    |   `-- css/style.css           Main application stylesheet
    |-- resources/
    |   |-- js/                     Echo and realtime browser logic
    |   |-- react/                  React player portal
    |   `-- views/                  Blade templates
    |-- routes/
    |   |-- channels.php            Private broadcast authorization
    |   `-- web.php                 Application routes
    `-- tests/                      Automated tests
```

## Quick Start

The complete installation and environment guide is available in [SETUP.md](SETUP.md).

Basic local setup:

```powershell
Set-Location src
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

Create a MySQL database named `badnet`, import `src/database/badnet.sql`, and configure the database credentials in `src/.env`.

Then run:

```powershell
php artisan migrate
npm run build
php artisan serve
```

Open `http://localhost:8000`.

Email verification requires valid SMTP credentials. See [SETUP.md](SETUP.md#8-configure-email) for Gmail App Password configuration.

## Local Development

Laravel server:

```powershell
php artisan serve
```

Frontend development:

```powershell
npm run dev
```

Scheduled reminders and token cleanup:

```powershell
php artisan schedule:work
```

Database queue worker, when `QUEUE_CONNECTION=database`:

```powershell
php artisan queue:work --tries=3
```

## Testing

Run all automated tests:

```powershell
php artisan test
```

Additional verification:

```powershell
php artisan migrate:status
php artisan route:list
php artisan schedule:list
npm run build
```

## Demo Data

Set the following in `.env` to allow UI demonstration cards when the database contains limited data:

```dotenv
APP_DEMO_DATA=true
```

Production should use:

```dotenv
APP_DEMO_DATA=false
```

Optional database seeding:

```powershell
php artisan db:seed --class=BadmintonSeeder
```

Warning: the seeder removes existing posts and comments before generating new demonstration records. Use it only with a local or test database.

## Realtime Updates

BadNet supports realtime notification counters and betting pool movement through Pusher-compatible broadcasting.

Without Pusher credentials, the notification interface continues to work through periodic polling. Realtime setup instructions are documented in [SETUP.md](SETUP.md#13-realtime-notifications-and-pool-movement).

## Security Notes

- Passwords are stored using Laravel hashing.
- Email verification codes are hashed and expire after 10 minutes.
- Password reset links expire after 15 minutes.
- Betting stakes, payouts, and refunds use database transactions.
- Wallet operations are recorded in an immutable transaction ledger.
- Betting settlement uses unique references to prevent duplicate payouts.
- Administrative actions are recorded in audit logs.
- Deleted users, posts, and matches can be restored through soft deletion.
- Secrets must remain in `.env` and must never be committed.

## Production Checklist

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Set `APP_DEMO_DATA=false`.
- Configure HTTPS.
- Configure production MySQL, SMTP, and Pusher credentials.
- Run a persistent queue worker.
- Configure the Laravel scheduler cron entry.
- Build frontend assets with `npm run build`.
- Run `php artisan migrate --force`.
- Configure regular database backups.
- Ensure `storage` and `bootstrap/cache` are writable.

See [SETUP.md](SETUP.md#18-production-configuration) for the full deployment checklist.

## License

This project was developed for educational use.
