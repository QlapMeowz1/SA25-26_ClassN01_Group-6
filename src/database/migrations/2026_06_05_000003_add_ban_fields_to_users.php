<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_banned')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_banned')->default(false)->after('role');
            });
        }

        if (!Schema::hasColumn('users', 'banned_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('banned_at')->nullable()->after('is_banned');
            });
        }

        if (!Schema::hasColumn('users', 'ban_reason')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('ban_reason', 255)->nullable()->after('banned_at');
            });
        }
    }

    public function down(): void
    {
        foreach (['ban_reason', 'banned_at', 'is_banned'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
