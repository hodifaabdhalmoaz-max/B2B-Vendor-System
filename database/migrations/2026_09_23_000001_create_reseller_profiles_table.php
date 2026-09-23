<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('business_name')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('governorate')->nullable()->index();
            $table->string('group_name')->nullable()->index();
            $table->text('notes')->nullable();
            $table->string('status', 32)->default('active')->index();
            $table->boolean('reservation_enabled')->default(true)->index();
            $table->unsignedInteger('reservation_timeout_minutes')->nullable();
            $table->timestamps();

            $table->index(['status', 'reservation_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_profiles');
    }
};
