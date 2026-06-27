<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Referral;

class MetaController extends Controller
{
    public function __invoke()
    {
        return $this->index();
    }

    public function index()
    {
        return response()->json([
            'cities' => Property::query()->distinct()->orderBy('city')->pluck('city')->filter()->values(),
            'property_types' => [
                ['value' => 'apartment', 'label' => 'Apartment'],
                ['value' => 'villa', 'label' => 'Villa'],
                ['value' => 'builder_floor', 'label' => 'Builder Floor'],
                ['value' => 'penthouse', 'label' => 'Penthouse'],
                ['value' => 'plot', 'label' => 'Plot'],
                ['value' => 'studio', 'label' => 'Studio'],
            ],
            'amenities' => [
                'Covered Parking', 'Lift', 'Gym', 'Swimming Pool', '24x7 Security',
                'Power Backup', 'Clubhouse', 'Kids Play Area', 'Garden', 'Gas Pipeline',
            ],
            'reward_tiers' => Referral::REWARD_TIERS,
        ]);
    }
}
