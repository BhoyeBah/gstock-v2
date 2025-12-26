<?php

namespace App\Http\Controllers;

use App\Http\Requests\WalletRequest;
use App\Models\Wallet;
use App\Models\walletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $wallets = Wallet::with(['transactions' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }])->get();
        return view('back.wallets.index', compact('wallets'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WalletRequest $request)
    {
        //

        $wallet = Wallet::create([
            'name' => $request->name,
            'code' => $request->code,
            'identifier' => $request->identifier,
            'initial_balance' => $request->initial_balance,
            'current_balance' => $request->initial_balance,
            'type' => $request->type,
        ]);

        return back()->with('success', 'Wallet crée avec succés');
    }

    public function transfert(Request $request)
    {
        $request->validate([
            'from_wallet_id' => 'required|exists:wallets,id',
            'to_wallet_id' => 'required|exists:wallets,id|different:from_wallet_id',
            'amount' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($request) {

            $fromWallet = Wallet::lockForUpdate()->findOrFail($request->from_wallet_id);
            $toWallet = Wallet::lockForUpdate()->findOrFail($request->to_wallet_id);
            $amount = $request->amount;

            if ($fromWallet->current_balance < $amount) {
                abort(422, 'Solde insuffisant');
            }

            // ===== DÉBIT =====
            $fromBefore = $fromWallet->current_balance;
            $fromWallet->decrement('current_balance', $amount);

            WalletTransaction::create([
                'wallet_id' => $fromWallet->id,
                'type' => 'out',
                'amount' => $amount,
                'balance_before' => $fromBefore,
                'balance_after' => $fromBefore - $amount,
                'source_type' => $fromWallet->name,
                'source_id' => $toWallet->id,
                'note' => 'Transfert vers '.$toWallet->name.'('.$fromWallet->identifier.')',
            ]);

            // ===== CRÉDIT =====
            $toBefore = $toWallet->current_balance;
            $toWallet->increment('current_balance', $amount);

            WalletTransaction::create([
                'wallet_id' => $toWallet->id,
                'type' => 'in',
                'amount' => $amount,
                'balance_before' => $toBefore,
                'balance_after' => $toBefore + $amount,
                'source_type' => $toWallet->name,
                'source_id' => $fromWallet->id,
                'note' => 'Transfert depuis '.$fromWallet->name.'('.$toWallet->identifier.')',
            ]);

        });

        return redirect()->back()->with('success', 'Transfert effectué avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(wallet $wallet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(wallet $wallet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, wallet $wallet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(wallet $wallet)
    {
        //
    }
}
