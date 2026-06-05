<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'max_members')) {
                $table->unsignedInteger('max_members')->default(20)->after('members_count');
            }

            if (!Schema::hasColumn('teams', 'level')) {
                $table->string('level', 60)->nullable()->after('max_members');
            }

            if (!Schema::hasColumn('teams', 'location')) {
                $table->string('location')->nullable()->after('level');
            }

            if (!Schema::hasColumn('teams', 'slogan')) {
                $table->string('slogan')->nullable()->after('location');
            }

            if (!Schema::hasColumn('teams', 'tags')) {
                $table->text('tags')->nullable()->after('slogan');
            }
        });
    }

    public function down(): void
    {
        foreach (['tags', 'slogan', 'location', 'level', 'max_members'] as $column) {
            if (Schema::hasColumn('teams', $column)) {
                Schema::table('teams', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
