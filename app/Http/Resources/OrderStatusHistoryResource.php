<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrderStatusHistory */
class OrderStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'changed_by' => $this->changed_by,
            'created_at' => $this->created_at,
        ];
    }
}
