<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Referral;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * All seed images are LICENSE-CLEAR PLACEHOLDERS (Lorem Picsum photos +
     * free sample equirectangular panoramas). Replace them with real uploads
     * via the app's upload flow. Nothing here is scraped from any third party.
     */
    public function run(): void
    {
        // ---------- Demo accounts (password: "password") ----------
        $buyer = User::create([
            'name' => 'Rahul Kapoor', 'email' => 'buyer@homesmenu.test', 'mobile' => '9820011223',
            'password' => 'password', 'role' => 'buyer', 'city' => 'Mumbai', 'is_verified' => true,
            'about' => 'First-time home buyer looking for a 2–3 BHK ready-to-move apartment in Mumbai.',
        ]);

        $seller = User::create([
            'name' => 'Sneha Patil', 'email' => 'seller@homesmenu.test', 'mobile' => '9830022114',
            'password' => 'password', 'role' => 'seller', 'city' => 'Pune', 'is_verified' => true,
        ]);

        $dealer = User::create([
            'name' => 'Anil Mehta', 'email' => 'dealer@homesmenu.test', 'mobile' => '9876543210',
            'password' => 'password', 'role' => 'dealer', 'city' => 'Mumbai', 'is_verified' => true,
        ]);

        $dealer2 = User::create([
            'name' => 'Rohit Sharma', 'email' => 'rohit@homesmenu.test', 'mobile' => '9988776655',
            'password' => 'password', 'role' => 'dealer', 'city' => 'Hyderabad', 'is_verified' => true,
        ]);

        $priya = User::create([
            'name' => 'Priya Nair', 'email' => 'priya@homesmenu.test', 'mobile' => '9911223344',
            'password' => 'password', 'role' => 'dealer', 'city' => 'Bengaluru', 'is_verified' => true,
        ]);

        $admin = User::create([
            'name' => 'Admin Team', 'email' => 'admin@homesmenu.test', 'mobile' => '9000000001',
            'password' => 'password', 'role' => 'admin', 'city' => 'Mumbai', 'is_verified' => true,
        ]);

        $owners = [$dealer, $dealer2, $priya, $seller];

        // ---------- Properties (realistic, author-written) ----------
        $panoBase = 'https://photo-sphere-viewer-data.netlify.app/assets/tour/key-biscayne-';
        $rooms = ['Living Hall', 'Master Bedroom', 'Kitchen', 'Balcony'];

        $catalogue = $this->catalogue();
        foreach ($catalogue as $i => $row) {
            $isAdminOwned = $i < 3;                      // admin posts the first 3 (shared inventory)
            $owner = $isAdminOwned ? $admin : $owners[$i % count($owners)];
            $pending = in_array($i, [8, 10]);            // two dealer listings await review
            $moderation = $isAdminOwned ? 'approved' : ($pending ? 'pending' : 'approved');

            $property = Property::create([
                'user_id' => $owner->id,
                'moderation_status' => $moderation,
                'approved_by' => $moderation === 'approved' ? $admin->id : null,
                'approved_at' => $moderation === 'approved' ? now() : null,
                'title' => $row['title'],
                'description' => $row['description'],
                'listing_type' => 'sale',
                'property_type' => $row['type'],
                'status' => $row['status'] ?? 'active',
                'construction_status' => $row['construction'] ?? 'ready',
                'price' => $row['price'],
                'area_sqft' => $row['area'],
                'bedrooms' => $row['beds'],
                'bathrooms' => $row['baths'],
                'furnishing' => $row['furnishing'] ?? 'Semi-Furnished',
                'facing' => $row['facing'] ?? 'East',
                'floor' => $row['floor'] ?? 8,
                'total_floors' => $row['total_floors'] ?? 24,
                'project' => $row['project'],
                'city' => $row['city'],
                'locality' => $row['locality'],
                'address' => $row['locality'].', '.$row['city'],
                'latitude' => $row['lat'],
                'longitude' => $row['lng'],
                'amenities' => $row['amenities'],
                'has_tour' => $row['tour'],
                'is_featured' => $row['featured'],
                'is_verified' => $row['verified'],
                'views' => random_int(180, 2400),
            ]);

            // Images — license-clear Lorem Picsum placeholders.
            for ($n = 1; $n <= 6; $n++) {
                $property->images()->create([
                    'path' => "https://picsum.photos/seed/pm-{$property->id}-{$n}/1280/860",
                    'is_cover' => $n === 1,
                    'sort_order' => $n,
                ]);
            }

            // 360° panoramas (free sample equirectangular images) for tour-enabled listings.
            if ($row['tour']) {
                foreach ($rooms as $r => $label) {
                    $property->panoramas()->create([
                        'room_label' => $label,
                        'image_path' => $panoBase.($r + 1).'.jpg',
                        'sort_order' => $r,
                    ]);
                }
            }

            // Sales contacts: admin inventory gets opted-in dealers; others list the owner.
            if ($isAdminOwned) {
                // Vary opt-ins so the demo dealer (Anil) has both "available to pick up"
                // (Skyline, Palm Grove) and "already selling" (Green Valley).
                $joinMap = [0 => [$dealer2, $priya], 1 => [$dealer, $dealer2], 2 => [$priya]];
                foreach (($joinMap[$i] ?? []) as $d) {
                    $property->propertyDealers()->create([
                        'dealer_id' => $d->id, 'contact_name' => $d->name,
                        'contact_phone' => '+91 '.$d->mobile, 'contact_email' => $d->email,
                        'pitch' => 'Verified dealer · site visits 7 days a week · best price assured.',
                        'is_active' => true,
                    ]);
                }
            } else {
                $property->propertyDealers()->create([
                    'dealer_id' => $owner->id, 'contact_name' => $owner->name,
                    'contact_phone' => '+91 '.$owner->mobile, 'contact_email' => $owner->email,
                    'pitch' => 'Listing owner', 'is_active' => true,
                ]);
            }
        }

        // ---------- Leads for the seller & dealers ----------
        $leadNames = [
            ['Karan Malhotra', '9876500001', 'refer_earn', 'new'],
            ['Divya Reddy', '9876500002', 'search', 'contacted'],
            ['Arjun Kapoor', '9876500003', 'virtual_tour', 'site_visit'],
            ['Meera Iyer', '9876500004', 'refer_earn', 'negotiating'],
            ['Sahil Verma', '9876500005', 'search', 'closed'],
        ];
        foreach ([$dealer, $seller, $dealer2] as $owner) {
            $prop = $owner->properties()->first();
            if (! $prop) {
                continue;
            }
            foreach ($leadNames as $idx => [$name, $phone, $source, $status]) {
                Lead::create([
                    'property_id' => $prop->id,
                    'seller_id' => $owner->id,
                    'name' => $name,
                    'phone' => '+91 '.$phone,
                    'source' => $source,
                    'status' => $status,
                    'created_at' => now()->subDays($idx),
                    'updated_at' => $status === 'closed' ? now()->subDays(random_int(1, 60)) : now()->subDays($idx),
                ]);
            }
        }

        // ---------- Refer & Earn history + wallet for the demo buyer ----------
        $tiers = Referral::REWARD_TIERS;
        $history = [
            ['Karan Malhotra', 'deal_closed', 'paid', $tiers['deal_closed']],
            ['Divya Reddy', 'site_visit', 'pending', $tiers['site_visit']],
            ['Meera Iyer', 'negotiating', 'in_progress', $tiers['deal_closed']],
            ['Sahil Verma', 'signed_up', 'paid', $tiers['signed_up']],
        ];
        foreach ($history as $idx => [$friend, $stage, $status, $reward]) {
            Referral::create([
                'referrer_id' => $buyer->id,
                'code' => $buyer->referral_code,
                'friend_name' => $friend,
                'stage' => $stage,
                'status' => $status,
                'reward' => $reward,
                'created_at' => now()->subDays($idx * 6),
            ]);
        }

        Transaction::create(['user_id' => $buyer->id, 'type' => 'referral_reward', 'amount' => 25000, 'status' => 'completed', 'description' => 'Referral reward — Karan M.', 'created_at' => now()->subDays(20)]);
        Transaction::create(['user_id' => $buyer->id, 'type' => 'referral_reward', 'amount' => 500, 'status' => 'completed', 'description' => 'Referral reward — Sahil V.', 'created_at' => now()->subDays(8)]);
        Transaction::create(['user_id' => $buyer->id, 'type' => 'withdrawal', 'amount' => -20000, 'status' => 'completed', 'description' => 'Withdrawal to HDFC ••4521', 'method' => 'Bank', 'created_at' => now()->subDays(15)]);
        $buyer->update(['wallet_balance' => 5500]);

        // ---------- Offline transactions (initiate -> confirm receipt) ----------
        $dealerProp = $dealer->properties()->first();
        $sellerProp = $seller->properties()->first();
        $pendingRef = $buyer->referrals()->where('status', 'in_progress')->first();

        // Buyer recorded a booking payment to the dealer — awaiting the DEALER's confirmation.
        if ($dealerProp) {
            Payment::create([
                'type' => 'property', 'initiated_by' => $buyer->id, 'payer_id' => $buyer->id,
                'payee_id' => $dealer->id, 'property_id' => $dealerProp->id,
                'amount' => 500000, 'method' => 'Bank Transfer', 'reference' => 'UTR8842190',
                'purpose' => 'Booking amount', 'note' => 'Token / booking for '.$dealerProp->title,
                'status' => 'initiated',
            ]);
        }

        // Dealer recorded a referral-reward payout to the buyer — awaiting the BUYER's confirmation.
        Payment::create([
            'type' => 'referral', 'initiated_by' => $dealer->id, 'payer_id' => $dealer->id,
            'payee_id' => $buyer->id, 'referral_id' => optional($pendingRef)->id,
            'amount' => 25000, 'method' => 'UPI', 'reference' => 'anilmehta@upi',
            'purpose' => 'Referral reward', 'note' => 'Reward for your closed referral',
            'status' => 'initiated',
        ]);

        // An already-confirmed payment (shows a completed record in history).
        if ($sellerProp) {
            Payment::create([
                'type' => 'property', 'initiated_by' => $buyer->id, 'payer_id' => $buyer->id,
                'payee_id' => $seller->id, 'property_id' => $sellerProp->id,
                'amount' => 200000, 'method' => 'Cash', 'purpose' => 'Token amount',
                'status' => 'confirmed', 'confirmed_at' => now()->subDays(4),
            ]);
        }

        // ---------- Buyer shortlist + a scheduled visit ----------
        $buyer->savedProperties()->syncWithoutDetaching(Property::take(3)->pluck('id'));
        $firstTour = Property::where('has_tour', true)->first();
        if ($firstTour) {
            $firstTour->siteVisits()->create([
                'user_id' => $buyer->id, 'name' => $buyer->name, 'phone' => '+91 '.$buyer->mobile,
                'visit_date' => Carbon::today()->addDays(3), 'time_slot' => '10:00 AM',
                'type' => 'in_person', 'status' => 'confirmed',
            ]);
        }
    }

    private function catalogue(): array
    {
        $A = ['Covered Parking', 'Lift', 'Gym', 'Swimming Pool', '24x7 Security', 'Power Backup', 'Clubhouse', 'Kids Play Area', 'Garden'];

        return [
            ['title' => '3 BHK Luxury Apartment', 'project' => 'Skyline Heights', 'city' => 'Mumbai', 'locality' => 'Bandra West', 'price' => 24500000, 'area' => 1240, 'beds' => 3, 'baths' => 3, 'type' => 'apartment', 'featured' => true, 'verified' => true, 'tour' => true, 'lat' => 19.0600, 'lng' => 72.8295, 'amenities' => $A, 'description' => 'A rare blend of privacy, light and skyline views in the heart of Bandra West. Wide-plank flooring, double-glazed windows and a sun-drenched east-facing balcony. RERA-approved and ready to move.'],
            ['title' => '2 BHK Modern Flat', 'project' => 'Green Valley Residency', 'city' => 'Bengaluru', 'locality' => 'Whitefield', 'price' => 9500000, 'area' => 1030, 'beds' => 2, 'baths' => 2, 'type' => 'apartment', 'featured' => true, 'verified' => true, 'tour' => true, 'construction' => 'new_launch', 'lat' => 12.9698, 'lng' => 77.7500, 'amenities' => $A, 'description' => 'A bright, efficiently-planned 2 BHK in a gated community minutes from the IT corridor. New launch with modern fittings and a clubhouse.'],
            ['title' => '4 BHK Independent Villa', 'project' => 'Palm Grove Estate', 'city' => 'Hyderabad', 'locality' => 'Gachibowli', 'price' => 38000000, 'area' => 2950, 'beds' => 4, 'baths' => 4, 'type' => 'villa', 'featured' => true, 'verified' => true, 'tour' => true, 'facing' => 'North-East', 'lat' => 17.4401, 'lng' => 78.3489, 'amenities' => $A, 'description' => 'A spacious gated villa with private garden, double-height living and premium imported fittings in Hyderabad’s most sought-after tech belt.'],
            ['title' => '1 BHK Studio Apartment', 'project' => 'Urban Nest', 'city' => 'Pune', 'locality' => 'Kharadi', 'price' => 6200000, 'area' => 720, 'beds' => 1, 'baths' => 1, 'type' => 'studio', 'featured' => true, 'verified' => false, 'tour' => false, 'lat' => 18.5510, 'lng' => 73.9470, 'amenities' => ['Covered Parking', 'Lift', 'Power Backup', 'Security'], 'description' => 'A smartly designed studio ideal for young professionals, walking distance to the EON IT park.'],
            ['title' => '3 BHK Builder Floor', 'project' => 'Royal Residency', 'city' => 'Gurugram', 'locality' => 'Sector 57', 'price' => 16500000, 'area' => 1500, 'beds' => 3, 'baths' => 3, 'type' => 'builder_floor', 'featured' => true, 'verified' => true, 'tour' => true, 'lat' => 28.4200, 'lng' => 77.0900, 'amenities' => $A, 'description' => 'A premium corner builder floor with a private terrace, modular kitchen and reserved parking in a quiet, well-connected sector.'],
            ['title' => '2 BHK Sea-View Flat', 'project' => 'Marine Crest', 'city' => 'Mumbai', 'locality' => 'Worli', 'price' => 42000000, 'area' => 1100, 'beds' => 2, 'baths' => 2, 'type' => 'apartment', 'featured' => true, 'verified' => true, 'tour' => true, 'facing' => 'West', 'floor' => 22, 'lat' => 19.0176, 'lng' => 72.8170, 'amenities' => $A, 'description' => 'Unobstructed Arabian Sea views from a high floor at Worli, steps from the Sea Link. Luxury fittings and a resort-style amenity deck.'],
            ['title' => '5 BHK Penthouse', 'project' => 'The Crown', 'city' => 'Hyderabad', 'locality' => 'Jubilee Hills', 'price' => 65000000, 'area' => 4010, 'beds' => 5, 'baths' => 5, 'type' => 'penthouse', 'featured' => false, 'verified' => true, 'tour' => true, 'floor' => 30, 'total_floors' => 30, 'lat' => 17.4239, 'lng' => 78.4738, 'amenities' => $A, 'description' => 'A duplex penthouse with a private plunge pool and panoramic city views in Jubilee Hills.'],
            ['title' => '3 BHK Lakeview Apartment', 'project' => 'Lakeshore Towers', 'city' => 'Mumbai', 'locality' => 'Powai', 'price' => 21000000, 'area' => 1000, 'beds' => 3, 'baths' => 2, 'type' => 'apartment', 'featured' => false, 'verified' => true, 'tour' => false, 'construction' => 'under_construction', 'lat' => 19.1176, 'lng' => 72.9060, 'amenities' => $A, 'description' => 'Overlooking Powai lake with green surroundings and excellent connectivity to the Eastern Express Highway.'],
            ['title' => '2 BHK Garden Apartment', 'project' => 'Prestige Meadows', 'city' => 'Bengaluru', 'locality' => 'Sarjapur Road', 'price' => 11500000, 'area' => 1180, 'beds' => 2, 'baths' => 2, 'type' => 'apartment', 'featured' => false, 'verified' => true, 'tour' => false, 'lat' => 12.9009, 'lng' => 77.6874, 'amenities' => $A, 'description' => 'Ground-floor apartment opening to a private landscaped garden in a premium Sarjapur Road township.'],
            ['title' => '4 BHK Sky Villa', 'project' => 'Aurum Sky', 'city' => 'Gurugram', 'locality' => 'Golf Course Road', 'price' => 52000000, 'area' => 3200, 'beds' => 4, 'baths' => 4, 'type' => 'penthouse', 'featured' => false, 'verified' => true, 'tour' => true, 'floor' => 28, 'total_floors' => 32, 'lat' => 28.4595, 'lng' => 77.0996, 'amenities' => $A, 'status' => 'under_offer', 'description' => 'A sky villa on Golf Course Road with private elevator access and floor-to-ceiling glazing.'],
            ['title' => '3 BHK Riverside Flat', 'project' => 'Riverdale', 'city' => 'Pune', 'locality' => 'Baner', 'price' => 14500000, 'area' => 1320, 'beds' => 3, 'baths' => 3, 'type' => 'apartment', 'featured' => false, 'verified' => false, 'tour' => false, 'lat' => 18.5590, 'lng' => 73.7868, 'amenities' => $A, 'description' => 'A well-ventilated 3 BHK in Baner with hill views and easy access to the Mumbai-Pune expressway.'],
            ['title' => '2 BHK Smart Home', 'project' => 'TechNest', 'city' => 'Hyderabad', 'locality' => 'Kondapur', 'price' => 8900000, 'area' => 990, 'beds' => 2, 'baths' => 2, 'type' => 'apartment', 'featured' => false, 'verified' => true, 'tour' => false, 'status' => 'sold', 'lat' => 17.4615, 'lng' => 78.3677, 'amenities' => $A, 'description' => 'A fully automated smart 2 BHK close to HITEC City, recently sold — a sample of a closed deal.'],
        ];
    }
}
