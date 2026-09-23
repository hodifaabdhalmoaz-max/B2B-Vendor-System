<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    /**
     * الحقول القابلة للتعبئة الجماعية
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'locality',
        'address',
        'city',
        'state',
        'country',
        'landmark',
        'zip',
        'type',
        'isdefault'
    ];

    /**
     * تحويل البيانات إلى الأنواع المناسبة
     *
     * @var array<string, string>
     */
    protected $casts = [
        'isdefault' => 'boolean',
    ];

    /**
     * الحصول على المستخدم المالك للعنوان
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * الحصول على العنوان الكامل
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->locality,
            $this->landmark,
            $this->city,
            $this->state,
            $this->country,
            $this->zip
        ]);

        return implode(', ', $parts);
    }

    /**
     * نطاق للحصول على العناوين الافتراضية
     */
    public function scopeDefault($query)
    {
        return $query->where('isdefault', true);
    }

    /**
     * نطاق للحصول على عناوين مستخدم معين
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
