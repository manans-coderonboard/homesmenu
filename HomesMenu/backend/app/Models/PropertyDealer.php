<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDealer extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'dealer_id', 'contact_name', 'contact_phone',
        'contact_email', 'pitch', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }
}
