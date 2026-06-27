<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Store an uploaded file on the local "public" disk.
     * Switch FILESYSTEM_DISK to s3 later — the rest of the app is disk-agnostic
     * (paths are stored; URLs are resolved via Storage::disk()->url()).
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'max:10240'], // 10 MB
            'type' => ['nullable', 'in:image,panorama,avatar'],
        ]);

        $type = $request->input('type', 'image');
        $folder = match ($type) {
            'panorama' => 'panoramas',
            'avatar' => 'avatars',
            default => 'properties',
        };

        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
        $path = $request->file('file')->store($folder, $disk);

        return response()->json([
            'path' => $path,
            'disk' => $disk,
            'url' => Storage::disk($disk)->url($path),
        ], 201);
    }
}
