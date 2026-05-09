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
use App\Models\GameMatch;
use App\Models\Challenge;
use App\Models\Post;

// Home
Route::get('/', function () {
    $posts = Post::with(['user', 'comments'])
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

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/{id}', [ProfileController::class, 'show'])->whereNumber('id')->name('profile.show');
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
    Route::post('/matches/{match}/bet', [MatchController::class, 'placeBet'])->name('matches.placeBet');
});

// Team Routes
Route::middleware('auth')->group(function () {
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::post('/teams/{team}/join', [TeamController::class, 'join'])->name('teams.join');
    Route::post('/teams/{team}/leave', [TeamController::class, 'leave'])->name('teams.leave');
});

// Tournament Routes
Route::middleware('auth')->group(function () {
    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/tournaments/create', [TournamentController::class, 'create'])->name('tournaments.create');
    Route::get('/tournaments/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
    Route::post('/tournaments', [TournamentController::class, 'store'])->name('tournaments.store');
    Route::post('/tournaments/{tournament}/join', [TournamentController::class, 'join'])->name('tournaments.join');
    Route::post('/tournaments/{tournament}/leave', [TournamentController::class, 'leave'])->name('tournaments.leave');
});

// Notifications (simple JSON endpoints for the header)
Route::middleware('auth')->group(function () {
    Route::get('/notifications/recent', [\App\Http\Controllers\NotificationsController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationsController::class, 'markAllRead'])->name('notifications.markAll');
});

// Post Routes
Route::middleware('auth')->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('/posts/load-more', [PostController::class, 'loadMore'])->name('posts.loadMore');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::post('/posts/{post}/delete', [PostController::class, 'delete'])->name('posts.delete');
    Route::post('/posts/{post}/like', [PostController::class, 'like'])->name('posts.like');
    Route::post('/posts/{post}/comment', [PostController::class, 'comment'])->name('posts.comment');
    Route::post('/comments/{comment}/delete', [PostController::class, 'deleteComment'])->name('comments.delete');
});
