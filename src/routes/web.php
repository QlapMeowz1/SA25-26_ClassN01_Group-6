<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AccountSecurityController;
use App\Models\GameMatch;
use App\Models\Challenge;
use App\Models\Post;

Route::get('/player-portal', fn () => view('player-portal'))->name('player.portal');

// Home
Route::get('/', function () {
    $posts = Post::with(['user', 'comments.user'])
        ->latest()
        ->limit(3)
        ->get();

    $openMatches = collect();
    $isFirstRun = false;

    if (auth()->check()) {
        $user = auth()->user();

        $hasMatches = GameMatch::where(function ($query) use ($user) {
            $query->where('player1_id', $user->id)
                ->orWhere('player2_id', $user->id);
        })->exists();

        $hasChallenges = Challenge::where(function ($query) use ($user) {
            $query->where('challenger_id', $user->id)
                ->orWhere('opponent_id', $user->id);
        })->exists();

        $hasPosts = Post::where('user_id', $user->id)->exists();
        $isFirstRun = !$hasMatches && !$hasChallenges && !$hasPosts;

        $openMatches = GameMatch::with('player1')
            ->where('status', 'open')
            ->whereNull('player2_id')
            ->where('player1_id', '!=', $user->id)
            ->latest()
            ->limit(4)
            ->get();
    }

    return view('home', compact('posts', 'openMatches', 'isFirstRun'));
})->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])
    ->middleware('throttle:5,1')
    ->name('password.update');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify-code', [\App\Http\Controllers\EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verify-code', [\App\Http\Controllers\EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/verification-code/resend', [\App\Http\Controllers\EmailVerificationController::class, 'resend'])->name('verification.resend');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/players', [\App\Http\Controllers\Admin\PlayersController::class, 'index'])->name('players');
    Route::get('/players/create', [\App\Http\Controllers\Admin\PlayersController::class, 'create'])->name('players.create');
    Route::post('/players', [\App\Http\Controllers\Admin\PlayersController::class, 'store'])->name('players.store');
    Route::post('/players/{user}/role', [\App\Http\Controllers\Admin\PlayersController::class, 'updateRole'])->name('players.role');
    Route::post('/players/{user}/wallet', [\App\Http\Controllers\Admin\PlayersController::class, 'updateWallet'])->name('players.wallet');
    Route::post('/players/{user}/ban', [\App\Http\Controllers\Admin\PlayersController::class, 'ban'])->name('players.ban');
    Route::post('/players/{user}/unban', [\App\Http\Controllers\Admin\PlayersController::class, 'unban'])->name('players.unban');
    Route::delete('/players/{user}', [\App\Http\Controllers\Admin\PlayersController::class, 'destroy'])->name('players.destroy');
    Route::post('/players/{user}/restore', [\App\Http\Controllers\Admin\PlayersController::class, 'restore'])->name('players.restore');
    Route::post('/players/bulk', [\App\Http\Controllers\Admin\PlayersController::class, 'bulk'])->name('players.bulk');
    Route::get('/players-export', [\App\Http\Controllers\Admin\PlayersController::class, 'export'])->name('players.export');
    Route::get('/users', fn () => redirect()->route('admin.players'))->name('users');
    Route::get('/tournaments', [\App\Http\Controllers\Admin\TournamentsController::class, 'index'])->name('tournaments');
    Route::get('/tournaments/create', [\App\Http\Controllers\Admin\TournamentsController::class, 'create'])->name('tournaments.create');
    Route::post('/tournaments', [\App\Http\Controllers\Admin\TournamentsController::class, 'store'])->name('tournaments.store');
    Route::get('/schedule', [\App\Http\Controllers\Admin\ScheduleController::class, 'index'])->name('schedule');
    Route::get('/court-bookings', [\App\Http\Controllers\Admin\CourtBookingsController::class, 'index'])->name('court-bookings');
    Route::get('/court-bookings/create', [\App\Http\Controllers\Admin\CourtBookingsController::class, 'create'])->name('court-bookings.create');
    Route::post('/court-bookings', [\App\Http\Controllers\Admin\CourtBookingsController::class, 'store'])->name('court-bookings.store');
    Route::get('/betting', [\App\Http\Controllers\Admin\BettingController::class, 'index'])->name('betting');
    Route::post('/betting/matches/{match}/odds', [\App\Http\Controllers\Admin\BettingController::class, 'updateOdds'])->name('betting.odds.update');
    Route::delete('/betting/matches/{match}/odds', [\App\Http\Controllers\Admin\BettingController::class, 'deleteOdds'])->name('betting.odds.delete');
    Route::post('/betting/matches/{match}/approve', [\App\Http\Controllers\Admin\BettingController::class, 'approve'])->name('betting.approve');
    Route::post('/betting/matches/{match}/cancel', [\App\Http\Controllers\Admin\BettingController::class, 'cancel'])->name('betting.cancel');
    Route::get('/statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index'])->name('statistics');
    Route::get('/bets', fn () => redirect()->route('admin.betting'))->name('bets');
    Route::get('/matches', [\App\Http\Controllers\AdminController::class, 'matches'])->name('matches');
    Route::get('/content', [\App\Http\Controllers\AdminController::class, 'content'])->name('content');
    Route::post('/posts/{post}/delete', [\App\Http\Controllers\AdminController::class, 'destroyPost'])->name('posts.delete');
    Route::post('/posts/{post}/restore', [\App\Http\Controllers\AdminController::class, 'restorePost'])->name('posts.restore');
    Route::post('/comments/{comment}/delete', [\App\Http\Controllers\AdminController::class, 'destroyComment'])->name('comments.delete');
    Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit');
    Route::delete('/matches/{match}', [\App\Http\Controllers\AdminController::class, 'destroyMatch'])->name('matches.destroy');
    Route::post('/matches/{match}/restore', [\App\Http\Controllers\AdminController::class, 'restoreMatch'])->name('matches.restore');
    Route::post('/matches/{match}/resolve-dispute', [\App\Http\Controllers\Admin\ScheduleController::class, 'resolveDispute'])->name('matches.resolve-dispute');
});

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/portal/betting', [\App\Http\Controllers\PortalBettingController::class, 'index'])->name('portal.betting.index');
    Route::post('/portal/betting', [\App\Http\Controllers\PortalBettingController::class, 'store'])->name('portal.betting.store');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/{id}', [ProfileController::class, 'show'])->whereNumber('id')->name('profile.show');
    Route::post('/settings/password', [AccountSecurityController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/settings/logout-other-devices', [AccountSecurityController::class, 'logoutOtherDevices'])->name('settings.sessions.logout-others');
});

// Challenge Routes
Route::middleware('auth')->group(function () {
    Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
    Route::get('/challenges/create', [ChallengeController::class, 'create'])->name('challenges.create');
    Route::post('/challenges', [ChallengeController::class, 'store'])->name('challenges.store');
    Route::post('/challenges/quick', [ChallengeController::class, 'quickChallenge'])->name('challenges.quick');
    Route::post('/challenges/{challenge}/request-join', [ChallengeController::class, 'requestJoin'])->name('challenges.requestJoin');
    Route::post('/challenges/{challenge}/requests/{joinRequest}/accept', [ChallengeController::class, 'acceptRequest'])->name('challenges.requests.accept');
    Route::post('/challenges/{challenge}/requests/{joinRequest}/reject', [ChallengeController::class, 'rejectRequest'])->name('challenges.requests.reject');
    Route::post('/challenges/{challenge}/accept', [ChallengeController::class, 'accept'])->name('challenges.accept');
    Route::post('/challenges/{challenge}/reject', [ChallengeController::class, 'reject'])->name('challenges.reject');
});

// Match Routes
Route::middleware('auth')->group(function () {
    Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
    Route::get('/matches/create', [MatchController::class, 'create'])->name('matches.create');
    Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
    Route::post('/matches/quick', [MatchController::class, 'quickMatch'])->name('matches.quick');
    Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
    Route::post('/matches/{match}/request-join', [MatchController::class, 'requestJoin'])->name('matches.requestJoin');
    Route::post('/matches/{match}/requests/{joinRequest}/accept', [MatchController::class, 'acceptRequest'])->name('matches.requests.accept');
    Route::post('/matches/{match}/requests/{joinRequest}/reject', [MatchController::class, 'rejectRequest'])->name('matches.requests.reject');
    Route::post('/matches/{match}/start', [MatchController::class, 'startMatch'])->name('matches.start');
    Route::post('/matches/{match}/result', [MatchController::class, 'submitResult'])->name('matches.submitResult');
    Route::post('/matches/{match}/result/confirm', [MatchController::class, 'confirmResult'])->name('matches.confirmResult');
    Route::post('/matches/{match}/result/dispute', [MatchController::class, 'disputeResult'])->name('matches.disputeResult');
    Route::post('/matches/{match}/odds', [MatchController::class, 'updateOdds'])->name('matches.odds.update');
    Route::delete('/matches/{match}/odds', [MatchController::class, 'deleteOdds'])->name('matches.odds.delete');
    Route::post('/matches/{match}/bet', [MatchController::class, 'placeBet'])->name('matches.placeBet');
});

// Team Routes
Route::middleware('auth')->group(function () {
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::post('/teams/sample/join', [TeamController::class, 'joinSample'])->name('teams.sampleJoin');
    Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.add');
    Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('teams.members.remove');
    Route::post('/teams/{team}/join', [TeamController::class, 'join'])->name('teams.join');
    Route::post('/teams/{team}/leave', [TeamController::class, 'leave'])->name('teams.leave');
});

// Tournament Routes
Route::middleware('auth')->group(function () {
    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/tournaments/create', [TournamentController::class, 'create'])->name('tournaments.create');
    Route::get('/tournaments/preview/{sampleId}', [TournamentController::class, 'preview'])->name('tournaments.preview');
    Route::get('/tournaments/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
    Route::post('/tournaments', [TournamentController::class, 'store'])->name('tournaments.store');
    Route::post('/tournaments/{tournament}/participants', [TournamentController::class, 'addParticipant'])->name('tournaments.participants.add');
    Route::delete('/tournaments/{tournament}/participants/{user}', [TournamentController::class, 'removeParticipant'])->name('tournaments.participants.remove');
    Route::post('/tournaments/{tournament}/join', [TournamentController::class, 'join'])->name('tournaments.join');
    Route::post('/tournaments/{tournament}/leave', [TournamentController::class, 'leave'])->name('tournaments.leave');
});

// Notifications (simple JSON endpoints for the header)
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationsController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [\App\Http\Controllers\NotificationsController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationsController::class, 'markRead'])->name('notifications.markRead');
    Route::post('/notifications/{notification}/unread', [\App\Http\Controllers\NotificationsController::class, 'markUnread'])->name('notifications.markUnread');
    Route::post('/notifications/{notification}/pin', [\App\Http\Controllers\NotificationsController::class, 'togglePin'])->name('notifications.togglePin');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationsController::class, 'markAllRead'])->name('notifications.markAll');
    Route::delete('/notifications/read', [\App\Http\Controllers\NotificationsController::class, 'clearRead'])->name('notifications.clearRead');
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationsController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/settings/notifications', [\App\Http\Controllers\NotificationsController::class, 'updatePreferences'])->name('notifications.preferences');
    
    // Betting routes
    Route::get('/bets/slip/{match}', [\App\Http\Controllers\BetController::class, 'slip'])->name('bets.slip');
    Route::get('/bets', [\App\Http\Controllers\BetController::class, 'index'])->name('bets.index');
    Route::get('/bets/{bet}', [\App\Http\Controllers\BetController::class, 'show'])->name('bets.show');
    
    // Theme routes
    Route::post('/api/theme/update', [ThemeController::class, 'update'])->name('theme.update');
    Route::get('/api/theme/get', [ThemeController::class, 'get'])->name('theme.get');
});

// Public Post Routes (index/show should be accessible to guests)
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/load-more', [PostController::class, 'loadMore'])->name('posts.loadMore');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

// Protected Post Routes
Route::middleware('auth')->group(function () {
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::post('/posts/{post}/delete', [PostController::class, 'delete'])->name('posts.delete');
    Route::post('/posts/{post}/like', [PostController::class, 'like'])->name('posts.like');
    Route::post('/posts/{post}/comment', [PostController::class, 'comment'])->name('posts.comment');
    Route::get('/posts/{post}/likes-count', [PostController::class, 'getLikesCount'])->name('posts.likesCount');
    Route::post('/comments/{comment}/reply', [PostController::class, 'replyComment'])->name('comments.reply');
    Route::post('/comments/{comment}/like', [PostController::class, 'toggleCommentLike'])->name('comments.like');
    Route::post('/comments/{comment}/delete', [PostController::class, 'deleteComment'])->name('comments.delete');
});
