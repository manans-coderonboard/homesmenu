<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $referrals = $user->referrals()
            ->with(['referredUser:id,name', 'property:id,title,project,city'])
            ->latest()
            ->get();

        return response()->json([
            'code' => $user->referral_code,
            'summary' => [
                'total_earned' => (float) $referrals->where('status', 'paid')->sum('reward'),
                'pending' => (float) $referrals->whereIn('status', ['pending', 'in_progress'])->sum('reward'),
                'successful' => $referrals->where('status', 'paid')->count(),
                'friends_joined' => $referrals->whereNotNull('referred_user_id')->count(),
            ],
            'tiers' => Referral::REWARD_TIERS,
            'data' => $referrals,
        ]);
    }

    /** Log a friend you've invited with your code. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'friend_name' => ['required', 'string', 'max:120'],
            'property_id' => ['nullable', 'exists:properties,id'],
        ]);

        $referral = $request->user()->referrals()->create([
            'code' => $request->user()->referral_code,
            'friend_name' => $data['friend_name'],
            'property_id' => $data['property_id'] ?? null,
            'stage' => 'signed_up',
            'status' => 'pending',
            'reward' => Referral::REWARD_TIERS['signed_up'],
        ]);

        return response()->json(['data' => $referral], 201);
    }
}
