<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PanoramaScene extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'room_label', 'image_path', 'disk',
        'default_yaw', 'default_pitch', 'sort_order',
    ];

    protected $appends = ['url'];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        return Storage::disk($this->disk ?: 'public')->url($this->image_path);
    }
}
