<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'listing_type' => $this->listing_type,
            'property_type' => $this->property_type,
            'property_type_label' => str($this->property_type)->replace('_', ' ')->title()->value(),
            'status' => $this->status,
            'moderation_status' => $this->moderation_status,
            'construction_status' => $this->construction_status,
            'price' => (float) $this->price,
            'price_label' => self::priceLabel((float) $this->price),
            'price_per_sqft' => $this->area_sqft ? round($this->price / $this->area_sqft) : null,
            'area_sqft' => $this->area_sqft,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'furnishing' => $this->furnishing,
            'facing' => $this->facing,
            'floor' => $this->floor,
            'total_floors' => $this->total_floors,
            'project' => $this->project,
            'city' => $this->city,
            'locality' => $this->locality,
            'address' => $this->address,
            'location' => trim(($this->locality ? $this->locality.', ' : '').$this->city, ', '),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'amenities' => $this->amenities ?? [],
            'virtual_tour_url' => $this->virtual_tour_url,
            'has_tour' => (bool) $this->has_tour,
            'is_featured' => (bool) $this->is_featured,
            'is_verified' => (bool) $this->is_verified,
            'views' => $this->views,
            'cover_image' => $this->coverUrl(),
            'images' => PropertyImageResource::collection($this->whenLoaded('images')),
            'panoramas' => PanoramaSceneResource::collection($this->whenLoaded('panoramas')),
            'dealers' => PropertyDealerResource::collection($this->whenLoaded('propertyDealers')),
            'property_dealers_count' => $this->whenCounted('propertyDealers'),
            'owner' => new DealerResource($this->whenLoaded('owner')),
            'is_saved' => $this->when(isset($this->is_saved), (bool) ($this->is_saved ?? false)),
            'created_at' => $this->created_at,
        ];
    }

    private function coverUrl(): ?string
    {
        if ($this->relationLoaded('cover') && $this->cover) {
            return $this->cover->url;
        }
        if ($this->relationLoaded('images') && $this->images->count()) {
            return ($this->images->firstWhere('is_cover', true) ?? $this->images->first())->url;
        }

        return null;
    }

    public static function priceLabel(float $price): string
    {
        $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');

        if ($price >= 10000000) {
            return '₹'.$fmt($price / 10000000).' Cr';
        }
        if ($price >= 100000) {
            return '₹'.$fmt($price / 100000).' Lakh';
        }

        return '₹'.number_format($price);
    }
}
