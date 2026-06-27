<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request, string $role)
    {
        return match ($role) {
            'seller' => $this->seller($request),
            'dealer' => $this->dealer($request),
            default => $this->buyer($request),
        };
    }

    protected function buyer(Request $request)
    {
        $user = $request->user();

        $recommended = Property::published()->where('is_featured', true)
            ->with(['cover', 'owner'])->latest()->take(4)->get();

        $saved = $user->savedProperties()->with(['cover', 'owner'])->take(3)->get()
            ->each(fn ($p) => $p->is_saved = true);

        return response()->json([
            'role' => 'buyer',
            'stats' => [
                'saved' => $user->savedProperties()->count(),
                'visits' => $user->siteVisits()->count(),
                'referrals' => $user->referrals()->count(),
                'wallet_balance' => (float) $user->wallet_balance,
            ],
            'recommended' => ['data' => PropertyResource::collection($recommended)],
            'saved' => ['data' => PropertyResource::collection($saved)],
            'upcoming_visits' => $user->siteVisits()->with('property:id,title,slug,city,locality')
                ->orderBy('visit_date')->take(5)->get(),
            'referral_code' => $user->referral_code,
        ]);
    }

    protected function seller(Request $request)
    {
        $user = $request->user();
        $props = $user->properties();

        return response()->json([
            'role' => 'seller',
            'stats' => [
                'total_listings' => (clone $props)->count(),
                'active_leads' => Lead::where('seller_id', $user->id)->whereNotIn('status', ['closed'])->count(),
                'total_views' => (int) (clone $props)->sum('views'),
                'sold' => (clone $props)->where('status', 'sold')->count(),
            ],
            'properties' => ['data' => PropertyResource::collection(
                $user->properties()->with('cover')->withCount('leads')->latest()->get()
            )],
            'leads' => Lead::where('seller_id', $user->id)
                ->with(['property:id,title,slug'])->latest()->take(10)->get(),
            'sold_value' => (float) (clone $props)->where('status', 'sold')->sum('price'),
        ]);
    }

    protected function dealer(Request $request)
    {
        $user = $request->user();
        $props = $user->properties();

        $soldValue = (float) (clone $props)->where('status', 'sold')->sum('price');
        $commission = round($soldValue * 0.01); // 1% demo commission

        return response()->json([
            'role' => 'dealer',
            'stats' => [
                'total_listings' => (clone $props)->count(),
                'customer_leads' => Lead::where('seller_id', $user->id)->count(),
                'deals_closed' => (clone $props)->where('status', 'sold')->count()
                    + Lead::where('seller_id', $user->id)->where('status', 'closed')->count(),
                'commission_earned' => $commission,
            ],
            'performance' => $this->monthlyPerformance($user->id),
            'leads' => Lead::where('seller_id', $user->id)
                ->with(['property:id,title,slug'])->latest()->take(10)->get(),
            'top_listings' => ['data' => PropertyResource::collection(
                $user->properties()->with('cover')->orderByDesc('views')->take(3)->get()
            )],
        ]);
    }

    /** Closed-lead counts per month for the last 6 months (drives the bar chart). */
    protected function monthlyPerformance(int $sellerId): array
    {
        $out = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = Lead::where('seller_id', $sellerId)
                ->where('status', 'closed')
                ->whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->count();
            $out[] = ['month' => $month->format('M'), 'deals' => $count];
        }

        return $out;
    }
}
