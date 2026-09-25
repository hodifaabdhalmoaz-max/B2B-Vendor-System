<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

/** Uses the existing Laravel store and User's standard Notifiable relationship. */
class ResellerNotification extends DatabaseNotification
{
    protected $table = 'notifications';

    protected $fillable = ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'event_key', 'created_at', 'updated_at'];

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return ['data' => 'array', 'read_at' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    }
}
