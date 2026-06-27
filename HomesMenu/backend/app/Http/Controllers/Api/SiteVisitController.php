<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class SiteVisitController extends Controller
{
    /** Public: schedule a (virtual or in-person) site visit. */
    public function store(Request $request, Property $property)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot' => ['required', 'string', 'max:20'],
            'type' => ['nullable', 'in:in_person,video'],
        ]);

        $visit = $property->siteVisits()->create([
            'user_id' => optional($request->user('sanctum'))->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'visit_date' => $data['visit_date'],
            'time_slot' => $data['time_slot'],
            'type' => $data['type'] ?? 'in_person',
            'status' => 'requested',
        ]);

        // Surface it as a lead for the owner too.
        $property->leads()->create([
            'seller_id' => $property->user_id,
            'buyer_id' => optional($request->user('sanctum'))->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'source' => 'virtual_tour',
            'status' => 'site_visit',
            'message' => 'Requested a '.($data['type'] ?? 'in_person').' visit on '.$data['visit_date'].' ('.$data['time_slot'].').',
        ]);

        return response()->json([
            'message' => 'Your visit request has been received. The dealer will confirm shortly.',
            'visit_id' => $visit->id,
        ], 201);
    }

    /** A user's own scheduled visits. */
    public function index(Request $request)
    {
        $visits = $request->user()->siteVisits()
            ->with('property:id,title,slug,city,locality')
            ->latest('visit_date')
            ->get();

        return response()->json(['data' => $visits]);
    }
}
