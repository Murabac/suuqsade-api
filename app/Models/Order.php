<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'product_link',
    'product_note',
    'product_platform',
    'product_title',
    'product_description',
    'product_images',
    'product_metadata_status',
    'status',
    'batch_id',
    'item_cost',
    'service_fee_pct',
    'shipping_fee',
    'total_amount',
    'delivery_address',
    'tracking_note',
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'product_images' => 'array',
            'item_cost' => 'decimal:2',
            'service_fee_pct' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function recordStatus(OrderStatus $status, ?Admin $admin = null): OrderStatusHistory
    {
        $this->update(['status' => $status]);

        return $this->statusHistory()->create([
            'status' => $status,
            'changed_by' => $admin?->id,
        ]);
    }
}
