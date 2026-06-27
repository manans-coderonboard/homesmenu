<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dealers who have opted in to sell a property and listed their contact
        // details. A single (admin-posted) property can be sold by many dealers;
        // buyers see all of them on the property page.
        Schema::create('property_dealers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dealer_id')->constrained('users')->cascadeOnDelete();
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('contact_email')->nullable();
            $table->text('pitch')->nullable();        // short "why deal with me" note
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['property_id', 'dealer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_dealers');
    }
};
