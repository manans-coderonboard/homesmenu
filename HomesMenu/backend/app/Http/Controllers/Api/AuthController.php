<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:20', 'unique:users,mobile'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', 'in:buyer,seller,dealer'],
            'referral_code' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'password' => $data['password'],
            'role' => $data['role'],
        ]);

        $this->attachReferral($user, $data['referral_code'] ?? null);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken('api')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken('api')->plainTextToken,
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    /** Create a pending referral when a new user signs up with a code. */
    protected function attachReferral(User $user, ?string $code): void
    {
        if (! $code) {
            return;
        }

        $referrer = User::where('referral_code', $code)->first();
        if (! $referrer || $referrer->id === $user->id) {
            return;
        }

        $user->update(['referred_by' => $referrer->id]);

        Referral::create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $user->id,
            'code' => $code,
            'friend_name' => $user->name,
            'stage' => 'signed_up',
            'status' => 'pending',
            'reward' => Referral::REWARD_TIERS['signed_up'],
        ]);
    }
}
