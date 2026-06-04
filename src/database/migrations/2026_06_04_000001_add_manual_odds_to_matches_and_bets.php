<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('matches', 'player1_odds')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->decimal('player1_odds', 6, 2)->nullable()->after('elo_change');
            });
        }

        if (!Schema::hasColumn('matches', 'player2_odds')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->decimal('player2_odds', 6, 2)->nullable()->after('player1_odds');
            });
        }

        if (!Schema::hasColumn('matches', 'odds_updated_by')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->unsignedBigInteger('odds_updated_by')->nullable()->after('player2_odds');
            });
        }

        if (!Schema::hasColumn('matches', 'odds_updated_at')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->timestamp('odds_updated_at')->nullable()->after('odds_updated_by');
            });
        }

        if (!Schema::hasColumn('bets', 'odds')) {
            Schema::table('bets', function (Blueprint $table) {
                $table->decimal('odds', 6, 2)->nullable()->after('amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bets', 'odds')) {
            Schema::table('bets', function (Blueprint $table) {
                $table->dropColumn('odds');
            });
        }

        foreach (['player1_odds', 'player2_odds', 'odds_updated_by', 'odds_updated_at'] as $column) {
            if (Schema::hasColumn('matches', $column)) {
                Schema::table('matches', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
