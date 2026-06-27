<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PropertyDealerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dealer_id' => $this->dealer_id,
            'name' => $this->contact_name,
            'initials' => Str::of($this->contact_name)->explode(' ')->take(2)->map(fn ($p) => Str::substr($p, 0, 1))->implode(''),
            'phone' => $this->contact_phone,
            'email' => $this->contact_email,
            'pitch' => $this->pitch,
            'is_verified' => (bool) ($this->dealer?->is_verified),
            'role' => $this->dealer?->role,
        ];
    }
}
