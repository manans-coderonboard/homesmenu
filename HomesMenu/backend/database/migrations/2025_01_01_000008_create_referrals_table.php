<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();      // who shared the code
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete(); // the friend
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('friend_name')->nullable();
            $table->enum('stage', ['signed_up', 'site_visit', 'negotiating', 'deal_closed'])->default('signed_up');
            $table->enum('status', ['pending', 'in_progress', 'paid'])->default('pending');
            $table->decimal('reward', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
