<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phone_number' => $this->phone_number,
            'name' => $this->name,
            'language' => $this->language,
            'delivery_address' => $this->delivery_address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
