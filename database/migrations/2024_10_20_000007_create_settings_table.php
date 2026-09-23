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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('type')->default('text'); // text, textarea, select, checkbox, radio, file, etc.
            $table->text('options')->nullable(); // For select, checkbox, radio types
            $table->string('group')->default('general'); // general, store, payment, shipping, etc.
            $table->integer('order')->default(0);
            $table->boolean('is_translatable')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
