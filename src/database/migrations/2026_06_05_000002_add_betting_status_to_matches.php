<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('matches', 'betting_status')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->string('betting_status', 24)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('matches', 'betting_status')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->dropColumn('betting_status');
            });
        }
    }
};
