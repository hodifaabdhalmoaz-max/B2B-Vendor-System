<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('event_key')->nullable()->unique();
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'notifications_recipient_read_index');
            $table->index(['notifiable_type', 'notifiable_id', 'created_at'], 'notifications_recipient_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_recipient_read_index');
            $table->dropIndex('notifications_recipient_created_index');
            $table->dropUnique(['event_key']);
            $table->dropColumn('event_key');
        });
    }
};
