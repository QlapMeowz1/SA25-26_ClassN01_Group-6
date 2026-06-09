<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE matches MODIFY status ENUM('open','scheduled','in_progress','pending_confirmation','disputed','completed','cancelled') NOT NULL DEFAULT 'open'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('matches')
                ->whereIn('status', ['pending_confirmation', 'disputed'])
                ->update(['status' => 'in_progress']);
            DB::statement("ALTER TABLE matches MODIFY status ENUM('open','scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'open'");
        }
    }
};
