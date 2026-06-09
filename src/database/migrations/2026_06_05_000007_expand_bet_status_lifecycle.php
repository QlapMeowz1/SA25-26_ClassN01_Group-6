<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bets') || !Schema::hasColumn('bets', 'status')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE bets MODIFY status ENUM('pending','live','won','lost','refunded') NOT NULL DEFAULT 'pending'");
            return;
        }

        DB::statement("ALTER TABLE bets ALTER COLUMN status TYPE VARCHAR(30)");
    }

    public function down(): void
    {
        if (!Schema::hasTable('bets') || !Schema::hasColumn('bets', 'status')) {
            return;
        }

        DB::table('bets')->whereNotIn('status', ['pending', 'won', 'lost'])->update(['status' => 'pending']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bets MODIFY status ENUM('pending','won','lost') NOT NULL DEFAULT 'pending'");
        }
    }
};
