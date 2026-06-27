<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'balance' => (float) $user->wallet_balance,
            'transactions' => $user->transactions()->latest()->take(50)->get(),
            'totals' => [
                'credited' => (float) $user->transactions()->where('amount', '>', 0)->where('status', 'completed')->sum('amount'),
                'withdrawn' => (float) abs($user->transactions()->where('type', 'withdrawal')->where('status', 'completed')->sum('amount')),
            ],
        ]);
    }

    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:500'],
            'method' => ['required', 'string', 'max:60'],
            'reference' => ['nullable', 'string', 'max:80'],
        ]);

        $user = $request->user();

        if ($data['amount'] > (float) $user->wallet_balance) {
            throw ValidationException::withMessages(['amount' => ['Amount exceeds your available balance.']]);
        }

        $txn = DB::transaction(function () use ($user, $data) {
            $user->decrement('wallet_balance', $data['amount']);

            return Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => -1 * $data['amount'],
                'status' => 'completed', // demo: instant. Wire to a payout gateway in production.
                'description' => 'Withdrawal to '.$data['method'],
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
            ]);
        });

        return response()->json([
            'message' => 'Withdrawal of ₹'.number_format($data['amount']).' processed.',
            'balance' => (float) $user->fresh()->wallet_balance,
            'transaction' => $txn,
        ]);
    }
}
