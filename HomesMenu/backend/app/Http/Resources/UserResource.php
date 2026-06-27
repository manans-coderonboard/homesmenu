<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    // Return the user object directly (no top-level "data" wrapper), so /me,
    // /profile and login/register all expose a consistent flat user shape.
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'role' => $this->role,
            'avatar_url' => $this->avatar_url,
            'referral_code' => $this->referral_code,
            'city' => $this->city,
            'about' => $this->about,
            'wallet_balance' => (float) $this->wallet_balance,
            'is_verified' => (bool) $this->is_verified,
            'kyc_status' => $this->kyc_status,
            'created_at' => $this->created_at,
        ];
    }
}
