<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Property extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'slug', 'description', 'listing_type', 'property_type',
        'status', 'construction_status', 'price', 'area_sqft', 'bedrooms', 'bathrooms',
        'furnishing', 'facing', 'floor', 'total_floors', 'project', 'city', 'locality',
        'address', 'latitude', 'longitude', 'amenities', 'virtual_tour_url', 'has_tour',
        'is_featured', 'is_verified', 'views',
        'moderation_status', 'rejection_reason', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'amenities' => 'array',
        'price' => 'decimal:2',
        'latitude' => 'float',
        'longitude' => 'float',
        'has_tour' => 'boolean',
        'is_featured' => 'boolean',
        'is_verified' => 'boolean',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Property $property) {
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->title).'-'.strtolower(Str::random(5));
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ---- Relationships ----
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function cover(): HasOne
    {
        return $this->hasOne(PropertyImage::class)->where('is_cover', true);
    }

    public function panoramas(): HasMany
    {
        return $this->hasMany(PanoramaScene::class)->orderBy('sort_order');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** Dealers who have opted in to sell this property (with their contact details). */
    public function propertyDealers(): HasMany
    {
        return $this->hasMany(PropertyDealer::class)->where('is_active', true);
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class);
    }

    // ---- Query scopes (drive the listings/search filters) ----
    public function scopePublished(Builder $q): Builder
    {
        // Public = admin-approved AND past draft.
        return $q->where('moderation_status', 'approved')
            ->whereIn('status', ['active', 'under_offer', 'sold']);
    }

    public function scopeFilter(Builder $q, array $f): Builder
    {
        $q->when($f['q'] ?? null, fn ($q, $v) => $q->where(fn ($q) => $q
            ->where('title', 'like', "%$v%")
            ->orWhere('project', 'like', "%$v%")
            ->orWhere('locality', 'like', "%$v%")
            ->orWhere('city', 'like', "%$v%")));

        $q->when($f['city'] ?? null, fn ($q, $v) => $q->where('city', $v));
        $q->when($f['listing_type'] ?? null, fn ($q, $v) => $q->where('listing_type', $v));
        $q->when($f['property_type'] ?? null, fn ($q, $v) => $q->where('property_type', $v));
        $q->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v));
        $q->when($f['bedrooms'] ?? null, fn ($q, $v) => $q->where('bedrooms', '>=', (int) $v));
        $q->when($f['min_price'] ?? null, fn ($q, $v) => $q->where('price', '>=', (float) $v));
        $q->when($f['max_price'] ?? null, fn ($q, $v) => $q->where('price', '<=', (float) $v));
        $q->when(($f['verified'] ?? null) !== null && $f['verified'], fn ($q) => $q->where('is_verified', true));
        $q->when(($f['has_tour'] ?? null) !== null && $f['has_tour'], fn ($q) => $q->where('has_tour', true));

        return match ($f['sort'] ?? 'relevance') {
            'price_asc' => $q->orderBy('price'),
            'price_desc' => $q->orderByDesc('price'),
            'newest' => $q->latest(),
            default => $q->orderByDesc('is_featured')->orderByDesc('views')->latest(),
        };
    }
}
