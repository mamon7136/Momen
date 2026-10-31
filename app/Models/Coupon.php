<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use BelongsToStore;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'expires_at' => 'datetime', 'is_active' => 'boolean',
            'value' => 'decimal:2', 'min_order' => 'decimal:2', 'max_discount' => 'decimal:2'];
    }

    /** Returns null when valid, or an Arabic error message. */
    public function validationError(float $subtotal, ?Customer $customer = null): ?string
    {
        if (! $this->is_active) {
            return 'الكوبون غير مفعل';
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'الكوبون لم يبدأ بعد';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'انتهت صلاحية الكوبون';
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 'تم استنفاد عدد مرات استخدام الكوبون';
        }
        if ($this->min_order && $subtotal < (float) $this->min_order) {
            return 'الحد الأدنى للطلب لاستخدام الكوبون هو '.number_format((float) $this->min_order).' ج.س';
        }
        if ($customer && $this->per_customer_limit) {
            $used = Order::where('customer_id', $customer->id)->where('coupon_id', $this->id)
                ->where('status', '!=', 'cancelled')->count();
            if ($used >= $this->per_customer_limit) {
                return 'لقد استخدمت هذا الكوبون من قبل';
            }
        }

        return null;
    }

    public function discountFor(float $subtotal): float
    {
        $discount = match ($this->type) {
            'percent' => $subtotal * ((float) $this->value / 100),
            'fixed' => (float) $this->value,
            default => 0.0,
        };
        if ($this->max_discount) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }
}
