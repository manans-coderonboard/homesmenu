<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /** Full-page redirect to Google's consent screen. */
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /** Google redirects here; we upsert the user, mint a token, and bounce to the frontend. */
    public function callback()
    {
        $frontend = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth failed: '.$e->getMessage());

            return redirect()->away($frontend.'/login?error=google');
        }

        $user = User::firstOrNew(['email' => $googleUser->getEmail()]);
        $isNew = ! $user->exists;

        if ($isNew) {
            $user->name = $googleUser->getName() ?: 'Google User';
            $user->role = 'buyer';
            $user->email_verified_at = now();
        }

        $user->google_id = $googleUser->getId();
        $user->avatar_url = $user->avatar_url ?: $googleUser->getAvatar();
        $user->save();

        $token = $user->createToken('api')->plainTextToken;

        return redirect()->away(
            $frontend.'/auth/callback?token='.urlencode($token).'&new='.($isNew ? '1' : '0')
        );
    }
}
