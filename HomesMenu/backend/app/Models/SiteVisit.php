<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'user_id', 'name', 'phone',
        'visit_date', 'time_slot', 'type', 'status',
    ];

    protected $casts = ['visit_date' => 'date'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
