<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Offline transactions: money changes hands outside the app (cash / bank /
        // UPI). One party RECORDS the payment ("I paid you"), the other CONFIRMS
        // receipt. Covers property purchases/bookings and referral-reward payouts.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['property', 'referral']);
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('referral_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('method')->nullable();      // Cash / Bank Transfer / UPI / Cheque
            $table->string('reference')->nullable();    // UTR / cheque no / txn id
            $table->string('purpose')->nullable();      // Token / Booking / Full payment / Reward
            $table->text('note')->nullable();
            $table->enum('status', ['initiated', 'confirmed', 'disputed', 'cancelled'])->default('initiated');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['payer_id', 'status']);
            $table->index(['payee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
