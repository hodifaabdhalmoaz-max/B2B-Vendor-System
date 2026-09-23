<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * الحقول القابلة للتعبئة الجماعية
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'order_id',
        'price',
        'quantity',
        'options',
        'rstatus'
    ];

    /**
     * تحويل البيانات إلى الأنواع المناسبة
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'options' => 'array',
        'rstatus' => 'boolean',
    ];

    /**
     * الحصول على المنتج المرتبط بعنصر الطلب
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * الحصول على الطلب المرتبط بعنصر الطلب
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * الحصول على إجمالي سعر العنصر
     */
    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * نطاق للحصول على العناصر المرتجعة
     */
    public function scopeReturned($query)
    {
        return $query->where('rstatus', true);
    }

    /**
     * نطاق للحصول على العناصر غير المرتجعة
     */
    public function scopeNotReturned($query)
    {
        return $query->where('rstatus', false);
    }
}
