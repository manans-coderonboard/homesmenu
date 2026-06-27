<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id', 'referred_user_id', 'property_id', 'code',
        'friend_name', 'stage', 'status', 'reward',
    ];

    protected $casts = ['reward' => 'decimal:2'];

    /** Reward tiers (₹) keyed by stage — keep in sync with the frontend. */
    public const REWARD_TIERS = [
        'signed_up' => 500,
        'site_visit' => 5000,
        'negotiating' => 5000,
        'deal_closed' => 25000,
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
