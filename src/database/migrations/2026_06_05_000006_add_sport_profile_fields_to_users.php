<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'handedness')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('handedness', 20)->nullable()->after('bio');
            });
        }

        if (!Schema::hasColumn('users', 'playing_style')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('playing_style', 80)->nullable()->after('handedness');
            });
        }
    }

    public function down(): void
    {
        foreach (['playing_style', 'handedness'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
