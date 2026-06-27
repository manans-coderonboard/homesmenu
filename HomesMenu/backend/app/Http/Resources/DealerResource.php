<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class DealerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'initials' => Str::of($this->name)->explode(' ')->take(2)->map(fn ($p) => Str::substr($p, 0, 1))->implode(''),
            'role' => $this->role,
            'avatar_url' => $this->avatar_url,
            'is_verified' => (bool) $this->is_verified,
            'mobile' => $this->when($request->user() !== null, $this->mobile),
        ];
    }
}
