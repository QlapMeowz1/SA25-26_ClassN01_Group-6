<?php

namespace Tests\Feature;

use App\Models\GameMatch;
use App\Models\User;
use App\Services\BetService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BettingIntegrityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Schema::dropAllTables();
        parent::tearDown();
    }

    public function test_stake_and_payout_are_recorded_once(): void
    {
        $bettor = $this->user('Bettor', 1000);
        $playerOne = $this->user('Player One', 5000);
        $playerTwo = $this->user('Player Two', 5000);
        $match = GameMatch::create([
            'player1_id' => $playerOne->id,
            'player2_id' => $playerTwo->id,
            'status' => 'scheduled',
            'betting_status' => 'open',
            'match_date' => now()->addDay(),
            'location' => 'Court 1',
            'player1_odds' => 2,
            'player2_odds' => 2,
        ]);

        $service = app(BetService::class);
        $bet = $service->placeBet($bettor, $match, 100, $playerOne->id);
        $this->assertSame(900, (int) $bettor->fresh()->virtual_coins);

        $match->update(['status' => 'completed', 'winner_id' => $playerOne->id]);
        $service->settleBetsAfterMatch($match->fresh());
        $service->settleBetsAfterMatch($match->fresh());

        $this->assertSame(1100, (int) $bettor->fresh()->virtual_coins);
        $this->assertSame('won', $bet->fresh()->status);
        $this->assertSame(2, $bettor->walletTransactions()->count());
        $this->assertSame(1, $bettor->walletTransactions()->where('type', 'bet_payout')->count());
    }

    public function test_result_requires_opponent_confirmation_before_settlement(): void
    {
        $playerOne = $this->user('Player One', 5000);
        $playerTwo = $this->user('Player Two', 5000);
        $match = GameMatch::create([
            'player1_id' => $playerOne->id,
            'player2_id' => $playerTwo->id,
            'status' => 'in_progress',
            'betting_status' => 'locked',
            'match_date' => now(),
            'location' => 'Court 2',
        ]);

        $this->actingAs($playerOne)
            ->post(route('matches.submitResult', $match), [
                'player1_score' => 21,
                'player2_score' => 15,
                'winner_id' => $playerOne->id,
            ])
            ->assertRedirect();

        $this->assertSame('pending_confirmation', $match->fresh()->status);

        $this->actingAs($playerTwo)
            ->post(route('matches.confirmResult', $match))
            ->assertRedirect();

        $this->assertSame('completed', $match->fresh()->status);
        $this->assertSame($playerTwo->id, (int) $match->fresh()->result_confirmed_by);
    }

    private function user(string $name, int $coins): User
    {
        $user = User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)) . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'rank' => 'Intermediate',
            'elo_rating' => 1200,
            'virtual_coins' => $coins,
            'wins' => 0,
            'losses' => 0,
            'role' => 'user',
            'is_banned' => false,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('rank')->default('Beginner');
            $table->integer('elo_rating')->default(1200);
            $table->integer('virtual_coins')->default(5000);
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->string('role')->default('user');
            $table->boolean('is_banned')->default(false);
            $table->string('ban_reason')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('player1_id');
            $table->integer('player2_id')->nullable();
            $table->string('status');
            $table->dateTime('match_date');
            $table->string('location')->nullable();
            $table->integer('player1_score')->nullable();
            $table->integer('player2_score')->nullable();
            $table->integer('winner_id')->nullable();
            $table->integer('result_submitted_by')->nullable();
            $table->integer('result_confirmed_by')->nullable();
            $table->timestamp('result_submitted_at')->nullable();
            $table->timestamp('result_confirmed_at')->nullable();
            $table->text('result_dispute_reason')->nullable();
            $table->integer('elo_change')->default(0);
            $table->decimal('player1_odds', 6, 2)->nullable();
            $table->decimal('player2_odds', 6, 2)->nullable();
            $table->string('betting_status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('match_id');
            $table->integer('bet_on_user_id');
            $table->integer('amount');
            $table->decimal('odds', 6, 2)->nullable();
            $table->string('status')->default('pending');
            $table->integer('payout')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->string('settlement_key')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('actor_id')->nullable();
            $table->integer('bet_id')->nullable();
            $table->string('type');
            $table->integer('amount');
            $table->integer('balance_before');
            $table->integer('balance_after');
            $table->string('reference')->nullable()->unique();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('title');
            $table->text('message');
            $table->string('type')->nullable();
            $table->integer('related_user_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->string('target_url', 2048)->nullable();
            $table->timestamps();
        });
    }
}
