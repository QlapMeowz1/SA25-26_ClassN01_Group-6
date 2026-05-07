# BadNet - Badminton Social Network

A Laravel-based badminton social network platform for learning purposes. This application demonstrates comprehensive web development features including user authentication, challenge systems, match management, rankings with ELO calculation, teams, tournaments, social features, and virtual betting.

## System Requirements

- PHP 8.0+
- Composer
- MySQL (via XAMPP)
- Apache (via XAMPP)

## Installation Steps

### 1. Setup XAMPP

- Download and install XAMPP from https://www.apachefriends.org/
- Start Apache and MySQL services

### 2. Create Database

Open phpMyAdmin (http://localhost/phpmyadmin) and create a database named `badnet`:

```sql
CREATE DATABASE badnet;
```

### 3. Install Laravel Dependencies

Navigate to the src folder and install dependencies:

```powershell
Set-Location src
composer install
```

### 4. Configure Environment

Copy .env.example to .env (already provided):

```powershell
Copy-Item .env.example .env
```

Update the following in `.env`:
- `APP_KEY` - Already set in .env
- `DB_DATABASE=badnet`
- `DB_USERNAME=root`
- `DB_PASSWORD=` (leave empty for XAMPP)

### 5. Import SQL Schema

Open phpMyAdmin, select the `badnet` database, then use the Import tab to load:

`src/database/badnet.sql`

### 6. Create Public Directories

```powershell
New-Item -ItemType Directory -Force -Path public/avatars, public/logos | Out-Null
```

### 7. Start Development Server

```powershell
php artisan serve
```

The application will be available at: `http://localhost:8000`

## Features

### User Management
- User registration and login
- Profile management with avatar upload
- Track ELO rating and ranking
- View match history and statistics

### Challenge System
- Send challenges to players with similar ELO rating
- Accept or reject challenges
- Automatic match creation on acceptance
- Maximum 400 ELO difference for fair play

### Match Management
- Create matches with opponent and date
- Start match
- Submit match results (scores and winner)
- ELO rating updates automatically
- Virtual betting on matches

### Ranking System
- ELO-based ranking (K-factor: 32)
- Four rank tiers:
  - Beginner (< 1400 ELO)
  - Intermediate (1400-1599 ELO)
  - Advanced (1600-1799 ELO)
  - Professional (1800+ ELO)
- Live leaderboard

### Team System
- Create and join teams
- Team members management
- Teams have leaders
- View team statistics

### Tournament System
- Create tournaments with registration
- Set max participants and prize pool
- Tournament status tracking
- Participant leaderboard

### Social Features
- Create and share posts
- Comment on posts
- Like posts
- View community feed
- Delete own posts and comments

### Virtual Betting
- Bet on match outcomes using virtual coins
- Starting coins: 5,000
- Bet settlement after match completion
- Double payout for winning bets

### Notifications
- Challenge notifications
- Match notifications
- Comment and like notifications
- Post notifications

## Project Structure

```
src/
├── app/
│   ├── Http/Controllers/     # Request handlers
│   │   ├── AuthController.php
│   │   ├── ChallengeController.php
│   │   ├── MatchController.php
│   │   ├── TeamController.php
│   │   ├── TournamentController.php
│   │   ├── PostController.php
│   │   ├── ProfileController.php
│   │   └── DashboardController.php
│   ├── Models/               # Database models
│   │   ├── User.php
│   │   ├── Challenge.php
│   │   ├── Match.php
│   │   ├── Team.php
│   │   ├── TeamMember.php
│   │   ├── Tournament.php
│   │   ├── TournamentParticipant.php
│   │   ├── Post.php
│   │   ├── Comment.php
│   │   ├── PostLike.php
│   │   ├── Bet.php
│   │   └── Notification.php
│   └── Services/             # Business logic
│       └── EloService.php    # ELO calculation
├── database/
│   └── badnet.sql            # MySQL schema for import
├── resources/
│   └── views/                # Blade templates
│       ├── layout.blade.php
│       ├── home.blade.php
│       ├── dashboard.blade.php
│       ├── auth/
│       ├── profile/
│       ├── challenges/
│       ├── matches/
│       ├── teams/
│       ├── tournaments/
│       └── posts/
├── routes/
│   └── web.php               # Web routes
└── public/
    ├── css/style.css         # Main stylesheet
    ├── avatars/              # User avatars
    └── logos/                # Team logos
```

## Database Schema

### Users Table
- id, name, email, password, phone, rank, elo_rating, virtual_coins, wins, losses, bio, avatar

### Challenges Table
- id, challenger_id, opponent_id, status, message, expires_at

### Matches Table
- id, player1_id, player2_id, challenge_id, status, match_date, location, player1_score, player2_score, winner_id, elo_change

### Teams Table
- id, name, description, leader_id, logo, members_count

### Tournaments Table
- id, name, description, organizer_id, start_date, end_date, max_participants, status, prize_pool

### Posts Table
- id, user_id, content, likes_count

### Bets Table
- id, user_id, match_id, bet_on_user_id, amount, status, payout

### Notifications Table
- id, user_id, title, message, type, related_user_id, is_read

## ELO System Details

The ELO rating calculation uses the standard chess formula:

- K-factor: 32 points per game
- Expected Score: 1 / (1 + 10^((opponent_rating - player_rating) / 400))
- ELO Change: K × (Actual Score - Expected Score)

### Rank Transitions
- Beginner → Intermediate at 1400 ELO
- Intermediate → Advanced at 1600 ELO
- Advanced → Professional at 1800 ELO

## Key Business Logic

### Match Result Settlement
Uses MySQL transaction to ensure data integrity:
1. Update match scores and winner
2. Calculate ELO changes for both players
3. Update player rankings
4. Settle all bets on the match
5. Update virtual coin balances

### Challenge Logic
- Players can only challenge opponents within 400 ELO points
- Challenges expire after 7 days
- Accepting a challenge auto-creates a match
- Only pending challenges can be accepted/rejected

### Betting Logic
- Users can only bet with their virtual coins
- Bets are pending until match completion
- Winning bets pay 2x the bet amount
- Losing bets return 0

## Testing the Application

### Sample Flow
1. Register two users
2. View leaderboard on challenges page
3. Send challenge from User 1 to User 2
4. User 2 accepts challenge
5. Match is created automatically
6. Place bets on the match
7. User 1 starts and submits match result
8. Check updated ELO ratings
9. Verify bet settlements and coin updates

## Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP
- Check DB credentials in .env file
- Verify database exists: `badnet`

### File Upload Issues
- Check permissions on `public/avatars` and `public/logos`
- Ensure proper PHP configuration for file uploads

### SQL Import Issues
- Verify the `badnet` database exists in phpMyAdmin
- Import `src/database/badnet.sql` again if tables are missing

### Session Issues
- Clear browser cookies and session storage
- Refresh the application after re-login

## Notes for Development

- All passwords are hashed using Laravel's built-in Hash::make()
- Transactions are used for critical operations (match results, bets)
- Foreign keys enforce referential integrity
- Timestamps track creation and updates
- ELO calculations happen on match completion
- All forms use CSRF tokens for security

## Future Enhancements

- Real-time notifications with WebSockets
- Direct messaging between players
- Advanced tournament bracket system
- Match statistics and analytics
- Skill-based recommendations
- Team vs Team matches
- Achievement/Badge system
- Video upload for match reviews
