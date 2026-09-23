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
        Schema::table('users', function (Blueprint $table) {
            // حقول المصادقة الثنائية
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_enabled_at')->nullable()->after('two_factor_recovery_codes');
            
            // حقول أمنية إضافية
            $table->timestamp('last_login_at')->nullable()->after('two_factor_enabled_at');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->integer('failed_login_attempts')->default(0)->after('last_login_ip');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            
            // حقول تتبع الجلسات
            $table->json('active_sessions')->nullable()->after('locked_until');
            $table->boolean('force_password_change')->default(false)->after('active_sessions');
            $table->timestamp('password_changed_at')->nullable()->after('force_password_change');
            
            // فهارس للأداء
            $table->index(['email', 'failed_login_attempts']);
            $table->index(['last_login_at']);
            $table->index(['locked_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_enabled_at',
                'last_login_at',
                'last_login_ip',
                'failed_login_attempts',
                'locked_until',
                'active_sessions',
                'force_password_change',
                'password_changed_at',
            ]);
        });
    }
};
