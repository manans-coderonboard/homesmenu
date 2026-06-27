<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'initiated_by', 'payer_id', 'payee_id', 'property_id', 'referral_id',
        'amount', 'method', 'reference', 'purpose', 'note', 'status', 'confirmed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }
}
