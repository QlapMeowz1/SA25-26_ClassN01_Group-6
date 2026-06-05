<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('email_verification_codes')) {
            Schema::create('email_verification_codes', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id');
                $table->string('email');
                $table->string('code_hash');
                $table->timestamp('expires_at');
                $table->timestamp('consumed_at')->nullable();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->timestamps();

                $table->index('user_id');
                $table->index(['user_id', 'consumed_at', 'expires_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verification_codes');
    }
};
