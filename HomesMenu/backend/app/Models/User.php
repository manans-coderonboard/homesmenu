<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'mobile', 'password', 'role', 'google_id',
        'avatar_url', 'referral_code', 'referred_by', 'city', 'about',
        'wallet_balance', 'is_verified', 'kyc_status',
    ];

    protected $hidden = [
        'password', 'remember_token', 'google_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'wallet_balance' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateReferralCode();
            }
        });
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(4).random_int(1000, 9999));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    // ---- Relationships ----
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function savedProperties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'saved_properties')->withTimestamps();
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'seller_id');
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class);
    }
}
