<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeCreateRequest;
use App\Http\Requests\PaymentEmployeRequest;
use App\Models\Employe;
use App\Models\EmployeTransaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $employes = Employe::paginate(10);

        return view('back.employes.index', compact('employes'));
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
    public function store(EmployeCreateRequest $request)
    {
        //
        Employe::create($request->validated());

        return back()->with('success', 'Employé crée avec succés');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employe $employe)
    {
        // Si show_all=1, ne pas appliquer de filtre par défaut
        $showAll = request('show_all') == '1';

        // Récupérer les paramètres de filtre
        $filterType = request('filter_type');
        $startDate = request('start_date');
        $endDate = request('end_date');

        // Query pour les transactions
        $transactionsQuery = EmployeTransaction::where('employe_id', $employe->id);

        // Appliquer les filtres de dates SEULEMENT si startDate ET endDate sont fournis
        if ($startDate && $endDate) {
            $transactionsQuery->whereBetween('date', [$startDate, $endDate]);
        }

        // Appliquer le filtre de type
        if ($filterType) {
            $transactionsQuery->where('type', $filterType);
        }

        $transactions = $transactionsQuery->orderBy('date', 'desc')->get();

        // Calculs pour les statistiques (basés sur la période filtrée)
        $statsQuery = EmployeTransaction::where('employe_id', $employe->id);

        if ($startDate && $endDate) {
            $statsQuery->whereBetween('date', [$startDate, $endDate]);
        }

        $statsTransactions = $statsQuery->get();

        // Calculs
        $salaireBase = $employe->salary ?? 0;
        $primes = $statsTransactions->where('type', 'bonus')->sum('amount');
        $avances = $statsTransactions->where('type', 'advance')->sum('amount');
        $remboursements = $statsTransactions->where('type', 'advance_repayment')->sum('amount');
        $deductions = $statsTransactions->where('type', 'deduction')->sum('amount');
        $paymentSalary = $statsTransactions->where('type', 'salary_payment')->sum('amount');

        $salaireNet = $salaireBase + $primes - $avances + $remboursements - $deductions - $paymentSalary;

        $wallets = Wallet::all();

        return view('back.employes.show', compact(
            'employe',
            'transactions',
            'salaireBase',
            'primes',
            'avances',
            'remboursements',
            'deductions',
            'paymentSalary',
            'salaireNet',
            'wallets',
            'filterType',
            'showAll',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employe $employe)
    {
        //
        return view('back.employes.edit', compact('employe'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeCreateRequest $request, Employe $employe)
    {
        //
        $employe->update($request->validated());

        return redirect()
            ->route('employes.index')
            ->with('success', 'Employé modifié avec succès.');
    }

    public function toggleActive(Employe $employe)
    {
        $employe->is_active = ! $employe->is_active;
        $employe->save();

        // message success
        $message = $employe->is_active
            ? 'L\'employé a été activé avec succès.'
            : 'L\'employé a été désactivé avec succès.';

        return redirect()->back()->with('success', $message);
    }

    public function pay(PaymentEmployeRequest $request, Employe $employe)
    {
        $amount = (int) $request->input('amount');
        $type = $request->input('type');

        // Types : wallet bouge
        $debitTypes = ['salary_payment', 'advance', 'bonus'];
        $creditTypes = ['advance_repayment'];
        $walletRequiredTypes = array_merge($debitTypes, $creditTypes);

        try {
            DB::beginTransaction();

            $wallet = null;

            // Charger wallet seulement si requis
            if (in_array($type, $walletRequiredTypes, true)) {
                $wallet = Wallet::where('id', $request->input('wallet_id'))
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            // Vérifier solde seulement si débit
            if ($wallet && in_array($type, $debitTypes, true) && $wallet->current_balance < $amount) {
                DB::rollBack();

                return back()->with('error', 'Solde insuffisant dans le wallet '.$wallet->name);
            }

            // Appliquer impact wallet
            if ($wallet && in_array($type, $creditTypes, true)) {
                $wallet->increment('current_balance', $amount);
            } elseif ($wallet && in_array($type, $debitTypes, true)) {
                $wallet->decrement('current_balance', $amount);
            }
            // deduction : pas de wallet

            // Enregistrer la transaction
            EmployeTransaction::create([
                'employe_id' => $employe->id,
                'wallet_id' => $wallet?->id, // ✅ nullable pour deduction
                'amount' => $amount,
                'type' => $type,
                'date' => $request->input('date') ?? now(),
                'reference' => $request->input('reference') ?? ('PAY-'.strtoupper(Str::random(8))),
                'note' => $request->input('note') ?? ('Transaction employé '.$employe->full_name),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return back()->with('success', "Transaction de {$amount} FCFA enregistrée pour {$employe->full_name}.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employe $employe)
    {
        //
        $employe->delete();

        return back()->with('success', 'Employé supprimé avec succés');
    }
}
