<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user','moderator','betting_manager','admin','super_admin') NOT NULL DEFAULT 'user'");
        }

        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
                $table->index(['role', 'is_banned']);
            });
        }

        if (!Schema::hasColumn('posts', 'deleted_at')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->softDeletes();
                $table->index(['user_id', 'created_at']);
            });
        }

        if (!Schema::hasColumn('bets', 'settled_at')) {
            Schema::table('bets', function (Blueprint $table) {
                $table->timestamp('settled_at')->nullable()->after('payout');
                $table->string('settlement_key')->nullable()->unique()->after('settled_at');
                $table->index(['match_id', 'status']);
                $table->index(['user_id', 'created_at']);
            });
        }

        if (!Schema::hasColumn('matches', 'result_submitted_by')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->softDeletes();
                $table->integer('result_submitted_by')->nullable()->after('winner_id');
                $table->integer('result_confirmed_by')->nullable()->after('result_submitted_by');
                $table->timestamp('result_submitted_at')->nullable()->after('result_confirmed_by');
                $table->timestamp('result_confirmed_at')->nullable()->after('result_submitted_at');
                $table->text('result_dispute_reason')->nullable()->after('result_confirmed_at');
                $table->index(['status', 'match_date']);
                $table->index(['location', 'match_date']);
            });
        }

        if (DB::getDriverName() === 'mysql') {
            $notificationIndexes = collect(DB::select('SHOW INDEX FROM notifications'))->pluck('Key_name');
            Schema::table('notifications', function (Blueprint $table) use ($notificationIndexes) {
                if (!$notificationIndexes->contains('notifications_user_id_is_read_created_at_index')) {
                    $table->index(['user_id', 'is_read', 'created_at']);
                }
                if (!$notificationIndexes->contains('notifications_user_id_is_pinned_created_at_index')) {
                    $table->index(['user_id', 'is_pinned', 'created_at']);
                }
            });
        } else {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['user_id', 'is_read', 'created_at']);
                $table->index(['user_id', 'is_pinned', 'created_at']);
            });
        }

        Schema::dropIfExists('wallet_transactions');
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('actor_id')->nullable();
            $table->integer('bet_id')->nullable();
            $table->string('type', 40);
            $table->bigInteger('amount');
            $table->bigInteger('balance_before');
            $table->bigInteger('balance_after');
            $table->string('reference')->nullable()->unique();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('bet_id')->references('id')->on('bets')->nullOnDelete();
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('actor_id')->nullable();
            $table->string('action', 100);
            $table->string('subject_type')->nullable();
            $table->integer('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['action', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('login_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->string('email');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('successful')->default(false);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['user_id', 'created_at']);
            $table->index(['email', 'created_at']);
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unique();
            $table->boolean('interactions_web')->default(true);
            $table->boolean('matches_web')->default(true);
            $table->boolean('betting_web')->default(true);
            $table->boolean('system_web')->default(true);
            $table->boolean('critical_email')->default(true);
            $table->boolean('match_reminders')->default(true);
            $table->boolean('betting_updates')->default(true);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('login_activities');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('wallet_transactions');

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_read', 'created_at']);
            $table->dropIndex(['user_id', 'is_pinned', 'created_at']);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex(['status', 'match_date']);
            $table->dropIndex(['location', 'match_date']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'result_submitted_by',
                'result_confirmed_by',
                'result_submitted_at',
                'result_confirmed_at',
                'result_dispute_reason',
            ]);
        });

        Schema::table('bets', function (Blueprint $table) {
            $table->dropUnique(['settlement_key']);
            $table->dropIndex(['match_id', 'status']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropColumn(['settled_at', 'settlement_key']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_banned']);
            $table->dropColumn(['last_login_at', 'last_login_ip']);
            $table->dropSoftDeletes();
        });
    }
};
