<?php

namespace App\Http\Controllers;

use App\Http\Requests\WalletRequest;
use App\Models\Wallet;
use App\Models\walletTransaction as WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wallets = Wallet::all(); // scope tenant appliqué

        $transactions = WalletTransaction::with('wallet')
            ->whereHas('wallet') 
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('back.wallets.index', compact('wallets', 'transactions'));
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
        $tenantId = $request->user()->tenant_id;

        $request->validate([
            'from_wallet_id' => [
                'required',
                Rule::exists('wallets', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'to_wallet_id' => [
                'required',
                'different:from_wallet_id',
                Rule::exists('wallets', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'amount' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($request, $tenantId) {

            $fromWallet = Wallet::where('tenant_id', $tenantId)
                ->whereKey($request->from_wallet_id)
                ->lockForUpdate()
                ->firstOrFail();
            $toWallet = Wallet::where('tenant_id', $tenantId)
                ->whereKey($request->to_wallet_id)
                ->lockForUpdate()
                ->firstOrFail();
            $amount = $request->amount;

            if ($fromWallet->current_balance < $amount) {
                abort(422, 'Solde insuffisant');
            }

            // ===== DÉBIT =====
            $fromBefore = $fromWallet->current_balance;
            $fromWallet->decrement('current_balance', $amount);

            WalletTransaction::create([
                'tenant_id' => $tenantId,
                'wallet_id' => $fromWallet->id,
                'user_id' => $request->user()->id,
                'type' => 'out',
                'transaction_type' => 'wallet_transfer_out',
                'amount' => $amount,
                'balance_before' => $fromBefore,
                'balance_after' => $fromBefore - $amount,
                'source_type' => Wallet::class,
                'source_id' => $toWallet->id,
                'note' => 'Transfert vers '.$toWallet->name.'('.$fromWallet->identifier.')',
                'description' => 'Transfert vers '.$toWallet->name.'('.$fromWallet->identifier.')',
            ]);

            // ===== CRÉDIT =====
            $toBefore = $toWallet->current_balance;
            $toWallet->increment('current_balance', $amount);

            WalletTransaction::create([
                'tenant_id' => $tenantId,
                'wallet_id' => $toWallet->id,
                'user_id' => $request->user()->id,
                'type' => 'in',
                'transaction_type' => 'wallet_transfer_in',
                'amount' => $amount,
                'balance_before' => $toBefore,
                'balance_after' => $toBefore + $amount,
                'source_type' => Wallet::class,
                'source_id' => $fromWallet->id,
                'note' => 'Transfert depuis '.$fromWallet->name.'('.$toWallet->identifier.')',
                'description' => 'Transfert depuis '.$fromWallet->name.'('.$toWallet->identifier.')',
            ]);

        });

        return redirect()->back()->with('success', 'Transfert effectué avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(Wallet $wallet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Wallet $wallet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Wallet $wallet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wallet $wallet)
    {
        //
    }
}
