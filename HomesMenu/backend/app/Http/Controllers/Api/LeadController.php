<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /** Public: a buyer enquires about a property. */
    public function store(Request $request, Property $property)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:160'],
            'message' => ['nullable', 'string', 'max:1000'],
            'source' => ['nullable', 'in:search,refer_earn,virtual_tour,direct'],
        ]);

        $lead = $property->leads()->create([
            'seller_id' => $property->user_id,
            'buyer_id' => optional($request->user('sanctum'))->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'message' => $data['message'] ?? null,
            'source' => $data['source'] ?? 'search',
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'Your enquiry has been sent to the dealer.',
            'lead_id' => $lead->id,
        ], 201);
    }

    /** Seller / dealer inbox. */
    public function index(Request $request)
    {
        $leads = Lead::where('seller_id', $request->user()->id)
            ->with(['property:id,title,slug,city,locality', 'buyer:id,name'])
            ->latest()
            ->get();

        return response()->json(['data' => $leads]);
    }

    public function update(Request $request, Lead $lead)
    {
        abort_unless($lead->seller_id === $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', 'in:new,contacted,site_visit,negotiating,closed'],
        ]);

        $lead->update($data);

        return response()->json(['data' => $lead]);
    }
}
