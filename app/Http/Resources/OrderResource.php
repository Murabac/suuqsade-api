<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_link' => $this->product_link,
            'product_note' => $this->product_note,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'batch_id' => $this->batch_id,
            'item_cost' => $this->formatMoney($this->item_cost),
            'service_fee_pct' => $this->service_fee_pct,
            'shipping_fee' => $this->formatMoney($this->shipping_fee),
            'total_amount' => $this->formatMoney($this->total_amount),
            'delivery_address' => $this->delivery_address,
            'tracking_note' => $this->tracking_note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function formatMoney(mixed $amount): ?string
    {
        if ($amount === null) {
            return null;
        }

        return '$'.number_format((float) $amount, 2);
    }
}
