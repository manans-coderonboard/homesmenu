<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each scene = one 360° equirectangular photo of a room, used by the
        // Photo Sphere Viewer on the frontend (Bedroom / Kitchen / Hall / Balcony...).
        Schema::create('panorama_scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('room_label');         // "Living Hall", "Master Bedroom", ...
            $table->string('image_path');         // equirectangular 2:1 image
            $table->string('disk')->default('public');
            $table->decimal('default_yaw', 6, 2)->default(0);    // starting view angle
            $table->decimal('default_pitch', 6, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panorama_scenes');
    }
};
