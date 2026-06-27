<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PanoramaSceneResource;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $properties = Property::query()
            ->published()
            ->with(['cover', 'owner'])
            ->filter($request->only([
                'q', 'city', 'listing_type', 'property_type', 'status',
                'bedrooms', 'min_price', 'max_price', 'verified', 'has_tour', 'sort',
            ]))
            ->paginate(min((int) $request->input('per_page', 12), 48))
            ->withQueryString();

        $this->markSaved($properties->getCollection(), $request);

        return PropertyResource::collection($properties);
    }

    public function featured(Request $request)
    {
        $properties = Property::query()
            ->published()
            ->where('is_featured', true)
            ->with(['cover', 'owner'])
            ->latest()
            ->take((int) $request->input('limit', 8))
            ->get();

        $this->markSaved($properties, $request);

        return PropertyResource::collection($properties);
    }

    public function show(Request $request, Property $property)
    {
        $property->loadMissing(['images', 'panoramas', 'owner', 'propertyDealers.dealer']);
        $property->increment('views');
        $this->markSaved(collect([$property]), $request);

        // Flat object (lists are wrapped in {data:[]}, single objects are returned flat).
        return (new PropertyResource($property))->resolve();
    }

    public function panoramas(Property $property)
    {
        return PanoramaSceneResource::collection($property->panoramas()->get());
    }

    public function mine(Request $request)
    {
        $properties = $request->user()->properties()
            ->with('cover')
            ->withCount('leads')
            ->latest()
            ->get();

        return PropertyResource::collection($properties);
    }

    public function store(Request $request)
    {
        $this->authorizeSeller($request);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'listing_type' => ['required', 'in:sale,rent'],
            'property_type' => ['required', 'in:apartment,villa,builder_floor,penthouse,plot,studio'],
            'construction_status' => ['nullable', 'in:ready,under_construction,new_launch'],
            'status' => ['nullable', 'in:draft,active'],
            'price' => ['required', 'numeric', 'min:0'],
            'area_sqft' => ['nullable', 'integer', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'furnishing' => ['nullable', 'string', 'max:40'],
            'facing' => ['nullable', 'string', 'max:40'],
            'floor' => ['nullable', 'integer'],
            'total_floors' => ['nullable', 'integer'],
            'project' => ['nullable', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:80'],
            'locality' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'amenities' => ['nullable', 'array'],
            'virtual_tour_url' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array'],                 // array of stored paths
            'images.*' => ['string'],
            'cover_index' => ['nullable', 'integer'],
            'panoramas' => ['nullable', 'array'],
            'panoramas.*.room_label' => ['required_with:panoramas', 'string'],
            'panoramas.*.image_path' => ['required_with:panoramas', 'string'],
        ]);

        $isAdmin = $request->user()->role === 'admin';

        $property = DB::transaction(function () use ($data, $request, $isAdmin) {
            $property = $request->user()->properties()->create([
                ...collect($data)->except(['images', 'cover_index', 'panoramas'])->toArray(),
                'status' => $data['status'] ?? 'active',
                'has_tour' => ! empty($data['panoramas']) || ! empty($data['virtual_tour_url']),
                // Admin listings publish immediately; everyone else awaits review.
                'moderation_status' => $isAdmin ? 'approved' : 'pending',
                'approved_by' => $isAdmin ? $request->user()->id : null,
                'approved_at' => $isAdmin ? now() : null,
            ]);

            // Sellers/dealers list themselves as the first sales contact.
            if (! $isAdmin) {
                $property->propertyDealers()->create([
                    'dealer_id' => $request->user()->id,
                    'contact_name' => $request->user()->name,
                    'contact_phone' => $request->user()->mobile ?? '',
                    'contact_email' => $request->user()->email,
                    'pitch' => 'Listing owner',
                    'is_active' => true,
                ]);
            }

            foreach (($data['images'] ?? []) as $i => $path) {
                $property->images()->create([
                    'path' => $path,
                    'is_cover' => $i === (int) ($data['cover_index'] ?? 0),
                    'sort_order' => $i,
                ]);
            }

            foreach (($data['panoramas'] ?? []) as $i => $scene) {
                $property->panoramas()->create([
                    'room_label' => $scene['room_label'],
                    'image_path' => $scene['image_path'],
                    'sort_order' => $i,
                ]);
            }

            return $property;
        });

        return (new PropertyResource($property->load(['images', 'panoramas', 'owner'])))->resolve();
    }

    public function update(Request $request, Property $property)
    {
        abort_unless($property->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:draft,active,under_offer,sold'],
            'amenities' => ['nullable', 'array'],
            'virtual_tour_url' => ['nullable', 'string', 'max:255'],
        ]);

        $property->update($data);

        return (new PropertyResource($property->fresh(['images', 'panoramas', 'owner'])))->resolve();
    }

    public function destroy(Request $request, Property $property)
    {
        abort_unless($property->user_id === $request->user()->id, 403);
        $property->delete();

        return response()->json(['message' => 'Property deleted']);
    }

    // ---- helpers ----
    protected function authorizeSeller(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['seller', 'dealer', 'admin']), 403, 'Only sellers, dealers and admins can list properties.');
    }

    protected function markSaved($properties, Request $request): void
    {
        // Resolve the bearer token even on public routes (no auth middleware).
        $user = $request->user('sanctum') ?? $request->user();
        if (! $user || $properties->isEmpty()) {
            return;
        }
        $savedIds = $user->savedProperties()->pluck('properties.id')->all();
        $properties->each(fn ($p) => $p->is_saved = in_array($p->id, $savedIds));
    }
}
