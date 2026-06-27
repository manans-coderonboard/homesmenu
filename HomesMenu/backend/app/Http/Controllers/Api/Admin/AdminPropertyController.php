<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class AdminPropertyController extends Controller
{
    /** Super-admin overview counts. */
    public function stats()
    {
        return response()->json([
            'pending' => Property::where('moderation_status', 'pending')->count(),
            'approved' => Property::where('moderation_status', 'approved')->count(),
            'rejected' => Property::where('moderation_status', 'rejected')->count(),
            'total' => Property::count(),
            'dealers' => User::where('role', 'dealer')->count(),
            'sellers' => User::where('role', 'seller')->count(),
            'leads' => Lead::count(),
        ]);
    }

    /** All properties (any status), newest first — drives the review queue. */
    public function index(Request $request)
    {
        $properties = Property::query()
            ->with(['cover', 'owner'])
            ->withCount('propertyDealers')
            ->when($request->input('moderation_status'), fn ($q, $v) => $q->where('moderation_status', $v))
            ->when($request->input('q'), fn ($q, $v) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%$v%")->orWhere('city', 'like', "%$v%")->orWhere('project', 'like', "%$v%")))
            ->orderByRaw("FIELD(moderation_status,'pending','approved','rejected')")
            ->latest()
            ->paginate(min((int) $request->input('per_page', 12), 48))
            ->withQueryString();

        return PropertyResource::collection($properties);
    }

    public function show(Property $property)
    {
        return (new PropertyResource($property->load(['images', 'panoramas', 'owner', 'propertyDealers.dealer'])))->resolve();
    }

    /** Approve (publish) a listing. */
    public function approve(Request $request, Property $property)
    {
        $property->update([
            'moderation_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
            'status' => $property->status === 'draft' ? 'active' : $property->status,
        ]);

        return (new PropertyResource($property->fresh(['owner', 'cover'])))->resolve();
    }

    public function reject(Request $request, Property $property)
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $property->update([
            'moderation_status' => 'rejected',
            'rejection_reason' => $data['reason'] ?? 'Did not meet listing guidelines.',
        ]);

        return (new PropertyResource($property->fresh(['owner', 'cover'])))->resolve();
    }

    /** Enhance / edit any property (admin can correct & enrich a dealer's listing). */
    public function update(Request $request, Property $property)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'area_sqft' => ['nullable', 'integer', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'furnishing' => ['nullable', 'string', 'max:40'],
            'facing' => ['nullable', 'string', 'max:40'],
            'project' => ['nullable', 'string', 'max:120'],
            'city' => ['sometimes', 'string', 'max:80'],
            'locality' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string'],
            'amenities' => ['nullable', 'array'],
            'virtual_tour_url' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_verified' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:draft,active,under_offer,sold'],
        ]);

        $property->update($data);

        return (new PropertyResource($property->fresh(['images', 'panoramas', 'owner', 'propertyDealers.dealer'])))->resolve();
    }
}
