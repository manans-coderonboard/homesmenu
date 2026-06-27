<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PropertyImage extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'path', 'disk', 'caption', 'is_cover', 'sort_order'];

    protected $casts = ['is_cover' => 'boolean'];

    protected $appends = ['url'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }
        // Absolute URL already stored (e.g. seeded placeholder)?
        if (str_starts_with($this->path, 'http')) {
            return $this->path;
        }

        return Storage::disk($this->disk ?: 'public')->url($this->path);
    }
}
