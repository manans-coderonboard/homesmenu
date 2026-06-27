<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Referral;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    /** All payments the current user is part of (as payer or payee). */
    public function index(Request $request)
    {
        $me = $request->user()->id;

        $payments = Payment::where('payer_id', $me)->orWhere('payee_id', $me)
            ->with(['payer:id,name', 'payee:id,name', 'property:id,title,slug'])
            ->latest()
            ->get()
            ->map(fn (Payment $p) => $this->shape($p, $me));

        return response()->json([
            'data' => $payments->values(),
            // Payments waiting for THIS user to confirm they received the money.
            'awaiting_confirmation' => $payments->where('can_confirm', true)->values(),
        ]);
    }

    /** Record an offline payment you made ("I paid X"). The recipient must confirm. */
    public function store(Request $request)
    {
        $me = $request->user();

        $data = $request->validate([
            'type' => ['required', 'in:property,referral'],
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'string', 'max:40'],
            'reference' => ['nullable', 'string', 'max:80'],
            'purpose' => ['nullable', 'string', 'max:60'],
            'note' => ['nullable', 'string', 'max:500'],
            'property_id' => ['required_if:type,property', 'nullable', 'exists:properties,id'],
            'payee_code' => ['required_if:type,referral', 'nullable', 'string', 'max:20'],
            'referral_id' => ['nullable', 'exists:referrals,id'],
        ]);

        $payeeId = null;
        $propertyId = null;
        $referralId = $data['referral_id'] ?? null;

        if ($data['type'] === 'property') {
            $property = Property::findOrFail($data['property_id']);
            $payeeId = $property->user_id;       // the listing owner receives the money
            $propertyId = $property->id;
        } else {
            $payee = User::where('referral_code', $data['payee_code'])->first();
            if (! $payee) {
                throw ValidationException::withMessages(['payee_code' => ['No member found with that referral code.']]);
            }
            $payeeId = $payee->id;
        }

        if ($payeeId === $me->id) {
            throw ValidationException::withMessages(['payee_code' => ['You cannot record a payment to yourself.']]);
        }

        $payment = Payment::create([
            'type' => $data['type'],
            'initiated_by' => $me->id,
            'payer_id' => $me->id,
            'payee_id' => $payeeId,
            'property_id' => $propertyId,
            'referral_id' => $referralId,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'purpose' => $data['purpose'] ?? null,
            'note' => $data['note'] ?? null,
            'status' => 'initiated',
        ]);

        return response()->json([
            'message' => 'Payment recorded. Waiting for the recipient to confirm receipt.',
            'data' => $this->shape($payment->load(['payer:id,name', 'payee:id,name', 'property:id,title,slug']), $me->id),
        ], 201);
    }

    /** The recipient confirms they received the money. */
    public function confirm(Request $request, Payment $payment)
    {
        $me = $request->user();
        abort_unless($payment->payee_id === $me->id, 403, 'Only the recipient can confirm this payment.');
        abort_unless($payment->status === 'initiated', 422, 'This payment can no longer be confirmed.');

        DB::transaction(function () use ($payment, $me) {
            $payment->update(['status' => 'confirmed', 'confirmed_at' => now()]);

            // A confirmed referral payout credits the recipient's wallet ledger
            // and marks the underlying referral as paid.
            if ($payment->type === 'referral') {
                $me->increment('wallet_balance', $payment->amount);
                Transaction::create([
                    'user_id' => $me->id,
                    'type' => 'referral_reward',
                    'amount' => $payment->amount,
                    'status' => 'completed',
                    'description' => 'Referral payout received from '.$payment->payer->name,
                    'method' => $payment->method,
                    'reference' => $payment->reference,
                    'referral_id' => $payment->referral_id,
                ]);
                if ($payment->referral_id) {
                    Referral::where('id', $payment->referral_id)->update(['status' => 'paid']);
                }
            }
        });

        return response()->json([
            'message' => 'Payment confirmed.',
            'data' => $this->shape($payment->fresh(['payer:id,name', 'payee:id,name', 'property:id,title,slug']), $me->id),
        ]);
    }

    /** The recipient flags that they did NOT receive the money. */
    public function dispute(Request $request, Payment $payment)
    {
        $me = $request->user();
        abort_unless($payment->payee_id === $me->id, 403);
        abort_unless($payment->status === 'initiated', 422);

        $payment->update(['status' => 'disputed']);

        return response()->json(['message' => 'Payment marked as not received (disputed).', 'data' => $this->shape($payment->fresh(['payer:id,name', 'payee:id,name', 'property:id,title,slug']), $me->id)]);
    }

    /** The initiator cancels a payment they recorded by mistake. */
    public function cancel(Request $request, Payment $payment)
    {
        abort_unless($payment->initiated_by === $request->user()->id, 403);
        abort_unless($payment->status === 'initiated', 422);

        $payment->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Payment cancelled.']);
    }

    private function shape(Payment $p, int $me): array
    {
        return [
            'id' => $p->id,
            'type' => $p->type,
            'amount' => (float) $p->amount,
            'status' => $p->status,
            'method' => $p->method,
            'reference' => $p->reference,
            'purpose' => $p->purpose,
            'note' => $p->note,
            'direction' => $p->payer_id === $me ? 'outgoing' : 'incoming',
            'payer' => $p->payer?->name,
            'payee' => $p->payee?->name,
            'counterparty' => $p->payer_id === $me ? $p->payee?->name : $p->payer?->name,
            'property' => $p->property ? ['title' => $p->property->title, 'slug' => $p->property->slug] : null,
            'can_confirm' => $p->payee_id === $me && $p->status === 'initiated',
            'can_cancel' => $p->initiated_by === $me && $p->status === 'initiated',
            'created_at' => $p->created_at,
            'confirmed_at' => $p->confirmed_at,
        ];
    }
}
