<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Payment */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => '$'.number_format((float) $this->amount, 2),
            'method' => $this->method,
            'confirmed_at' => $this->confirmed_at,
        ];
    }
}
