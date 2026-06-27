<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // owner: seller or dealer
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->enum('listing_type', ['sale', 'rent'])->default('sale');
            $table->enum('property_type', ['apartment', 'villa', 'builder_floor', 'penthouse', 'plot', 'studio'])->default('apartment');
            $table->enum('status', ['draft', 'active', 'under_offer', 'sold'])->default('active');
            $table->enum('construction_status', ['ready', 'under_construction', 'new_launch'])->default('ready');

            // Moderation: dealer/seller listings start "pending" until the super-admin
            // team reviews/enhances and approves them. Admin listings are auto-approved.
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->decimal('price', 14, 2)->default(0);
            $table->unsignedInteger('area_sqft')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->string('furnishing')->nullable();      // Unfurnished / Semi / Furnished
            $table->string('facing')->nullable();
            $table->unsignedSmallInteger('floor')->nullable();
            $table->unsignedSmallInteger('total_floors')->nullable();

            $table->string('project')->nullable();
            $table->string('city')->index();
            $table->string('locality')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->json('amenities')->nullable();
            $table->string('virtual_tour_url')->nullable();
            $table->boolean('has_tour')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false); // RERA verified
            $table->unsignedInteger('views')->default(0);

            $table->timestamps();

            $table->index(['listing_type', 'status']);
            $table->index(['property_type', 'bedrooms']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
