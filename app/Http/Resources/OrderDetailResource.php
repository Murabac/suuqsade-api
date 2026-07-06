<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/** @mixin \App\Models\Order */
class OrderDetailResource extends OrderResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'status_history' => OrderStatusHistoryResource::collection(
                $this->whenLoaded('statusHistory'),
            ),
            'payments' => PaymentResource::collection(
                $this->whenLoaded('payments'),
            ),
        ]);
    }
}
