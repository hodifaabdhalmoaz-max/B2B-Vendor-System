<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('event_type'); // login, logout, password_change, 2fa_enable, etc.
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->json('event_data')->nullable();
            $table->string('risk_level')->default('low'); // low, medium, high, critical
            $table->boolean('blocked')->default(false);
            $table->string('location')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            // فهارس للأداء
            $table->index(['user_id', 'occurred_at']);
            $table->index(['event_type', 'occurred_at']);
            $table->index(['risk_level', 'occurred_at']);
            $table->index(['ip_address', 'occurred_at']);
            $table->index(['blocked', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_logs');
    }
};
