<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['referral_reward', 'withdrawal', 'bonus'])->default('referral_reward');
            $table->decimal('amount', 12, 2);          // signed: + credit / - debit
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->string('description')->nullable();
            $table->string('method')->nullable();      // bank / UPI
            $table->string('reference')->nullable();
            $table->foreignId('referral_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
