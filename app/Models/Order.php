<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use BelongsToStore;

    protected $guarded = ['id'];

    public const STATUSES = [
        'pending' => 'جديد',
        'awaiting_payment' => 'بانتظار الدفع',
        'payment_review' => 'مراجعة الدفع',
        'confirmed' => 'مؤكد',
        'processing' => 'قيد التجهيز',
        'ready' => 'جاهز للاستلام/الشحن',
        'shipped' => 'تم الشحن',
        'delivered' => 'تم التوصيل',
        'cancelled' => 'ملغي',
        'returned' => 'مسترجع',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'غير مدفوع',
        'pending_review' => 'إيصال قيد المراجعة',
        'paid' => 'مدفوع',
        'refunded' => 'مسترد',
        'failed' => 'فشل الدفع',
    ];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'shipping_fee' => 'decimal:2',
            'payment_fee' => 'decimal:2', 'total' => 'decimal:2', 'commission' => 'decimal:2', 'cod_deposit' => 'decimal:2'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('id');
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class)->latestOfMany();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    public function amountDue(): float
    {
        return $this->payment_status === 'paid' ? 0.0 : (float) $this->total;
    }

    public function trackingUrl(): string
    {
        return rtrim($this->store->primaryUrl(), '/').'/track/'.$this->tracking_token;
    }
}
