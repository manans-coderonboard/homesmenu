<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Request;

class SavedPropertyController extends Controller
{
    public function index(Request $request)
    {
        $properties = $request->user()
            ->savedProperties()
            ->with(['cover', 'owner'])
            ->latest('saved_properties.created_at')
            ->get()
            ->each(fn ($p) => $p->is_saved = true);

        return PropertyResource::collection($properties);
    }

    public function store(Request $request, Property $property)
    {
        $request->user()->savedProperties()->syncWithoutDetaching([$property->id]);

        return response()->json(['message' => 'Saved', 'saved' => true]);
    }

    public function destroy(Request $request, Property $property)
    {
        $request->user()->savedProperties()->detach($property->id);

        return response()->json(['message' => 'Removed', 'saved' => false]);
    }
}
