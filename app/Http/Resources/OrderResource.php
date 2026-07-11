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
            'product_platform' => $this->product_platform,
            'product_title' => $this->product_title,
            'product_description' => $this->product_description,
            'product_images' => $this->product_images ?? [],
            'product_metadata_status' => $this->product_metadata_status ?? 'pending',
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'batch_id' => $this->batch_id,
            'item_cost' => $this->formatMoney($this->item_cost),
            'service_fee_pct' => $this->service_fee_pct,
            'shipping_fee' => $this->formatMoney($this->shipping_fee),
            'total_amount' => $this->formatMoney($this->total_amount),
            'final_total' => $this->formatFinalTotal(),
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

    private function formatFinalTotal(): ?string
    {
        if ($this->shipping_fee === null || $this->item_cost === null) {
            return null;
        }

        $item = (float) $this->item_cost;
        $service = round($item * ((float) $this->service_fee_pct / 100), 2);
        $final = round($item + $service + (float) $this->shipping_fee, 2);

        return '$'.number_format($final, 2);
    }
}
