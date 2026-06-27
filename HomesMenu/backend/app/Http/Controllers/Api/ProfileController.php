<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'mobile' => ['sometimes', 'nullable', 'string', 'max:20', 'unique:users,mobile,'.$user->id],
            'city' => ['sometimes', 'nullable', 'string', 'max:80'],
            'about' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'role' => ['sometimes', 'in:buyer,seller,dealer'],
        ]);

        $user->update($data);

        return new UserResource($user->fresh());
    }

    public function avatar(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'image', 'max:5120']]);

        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
        $path = $request->file('file')->store('avatars', $disk);
        $url = Storage::disk($disk)->url($path);

        $request->user()->update(['avatar_url' => $url]);

        return new UserResource($request->user()->fresh());
    }
}
