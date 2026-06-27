<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyDealerController extends Controller
{
    /** Admin-listed inventory a dealer can pick up to sell (not owned/joined yet). */
    public function available(Request $request)
    {
        $me = $request->user()->id;

        $properties = Property::query()
            ->published()
            ->whereHas('owner', fn ($q) => $q->where('role', 'admin'))
            ->where('user_id', '!=', $me)
            ->whereDoesntHave('propertyDealers', fn ($q) => $q->where('dealer_id', $me))
            ->with(['cover', 'owner'])
            ->withCount('propertyDealers')
            ->latest()
            ->paginate(min((int) $request->input('per_page', 12), 48))
            ->withQueryString();

        return PropertyResource::collection($properties);
    }

    /** Properties this dealer has opted in to sell. */
    public function myListings(Request $request)
    {
        $me = $request->user()->id;

        $properties = Property::query()
            ->whereHas('propertyDealers', fn ($q) => $q->where('dealer_id', $me))
            ->with(['cover', 'owner'])
            ->withCount('propertyDealers')
            ->latest()
            ->get();

        return PropertyResource::collection($properties);
    }

    /** Opt in to sell a property, listing your contact details. */
    public function join(Request $request, Property $property)
    {
        abort_unless(in_array($request->user()->role, ['seller', 'dealer']), 403, 'Only dealers can pick up listings.');
        abort_if($property->user_id === $request->user()->id, 422, 'You already own this listing.');

        $data = $request->validate([
            'contact_name' => ['required', 'string', 'max:120'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:160'],
            'pitch' => ['nullable', 'string', 'max:300'],
        ]);

        $property->propertyDealers()->updateOrCreate(
            ['dealer_id' => $request->user()->id],
            [...$data, 'is_active' => true]
        );

        return response()->json(['message' => 'You are now listed as a dealer for this property.']);
    }

    public function leave(Request $request, Property $property)
    {
        $property->propertyDealers()->where('dealer_id', $request->user()->id)->delete();

        return response()->json(['message' => 'You have been removed from this property.']);
    }
}
