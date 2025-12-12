<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FrontController extends Controller
{
    public function index(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $period = $request->get('period', 'month'); // 'day', 'week', 'month'

        // Déterminer la période à utiliser (custom ou prédéfini)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $period = 'custom'; // Pour le select
        } else {
            [$start, $end] = match ($period) {
                'day' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
                'week' => [Carbon::now()->startOfWeek()->startOfDay(), Carbon::now()->endOfWeek()->endOfDay()],
                'year' => [Carbon::now()->startOfYear()->startOfDay(), Carbon::now()->endOfYear()->endOfDay()],
                'lastMonth' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
                default => [Carbon::now()->startOfMonth()->startOfDay(), Carbon::now()->endOfMonth()->endOfDay()],
            };
        }

        // Clé cache unique pour cette période
        $cacheKey = "dashboard_stats_{$tenant->id}_{$start->format('Ymd')}_{$end->format('Ymd')}";

        $stats = Cache::remember($cacheKey, 30, function () use ($tenant, $start, $end) {
            // Factures
            $invoiceStats = DB::table('invoices')
                ->selectRaw("
                    SUM(CASE WHEN type = 'client' AND status NOT IN ('draft','cancelled') THEN total_invoice ELSE 0 END) as total_ventes,
                    SUM(CASE WHEN type = 'supplier' AND status NOT IN ('draft','cancelled') THEN total_invoice ELSE 0 END) as total_achats,
                    COUNT(CASE WHEN type = 'client' AND status NOT IN ('draft','cancelled') THEN 1 END) as nb_factures_clients,
                    COUNT(CASE WHEN type = 'supplier' AND status NOT IN ('draft','cancelled') THEN 1 END) as nb_factures_fournisseurs
                ")
                ->where('tenant_id', $tenant->id)
                ->whereBetween('invoice_date', [$start, $end])
                ->first();

            // Comptage clients/fournisseurs
            $counts = DB::table('contacts')
                ->selectRaw("
                    COUNT(CASE WHEN type = 'client' THEN 1 END) as nb_clients,
                    COUNT(CASE WHEN type = 'supplier' THEN 1 END) as nb_fournisseurs
                ")
                ->where('tenant_id', $tenant->id)
                ->first();

            $nbProduits = DB::table('products')->where('tenant_id', $tenant->id)->count();
            $nbEntrepots = DB::table('warehouses')->where('tenant_id', $tenant->id)->count();

            // Paiements
            $paiements = DB::table('payments')
                ->selectRaw("
                    SUM(CASE WHEN payment_source = 'client' THEN amount_paid ELSE 0 END) as total_paiement_clients,
                    SUM(CASE WHEN payment_source = 'supplier' THEN amount_paid ELSE 0 END) as total_paiement_fournisseurs
                ")
                ->where('tenant_id', $tenant->id)
                ->whereBetween('payment_date', [$start, $end])
                ->first();

            // Balance
            $balance_clients = DB::table('invoices')
                ->where('tenant_id', $tenant->id)
                ->where('type', 'client')
                ->whereNotIn('status', ['draft','cancelled'])
                ->whereBetween('invoice_date', [$start, $end])
                ->sum('balance');

            $balance_fournisseurs = DB::table('invoices')
                ->where('tenant_id', $tenant->id)
                ->where('type', 'supplier')
                ->whereNotIn('status', ['draft','cancelled'])
                ->whereBetween('invoice_date', [$start, $end])
                ->sum('balance');

            // Dépenses
            $depenses = DB::table('expenses')
                ->where('tenant_id', $tenant->id)
                ->whereBetween('expense_date', [$start, $end])
                ->sum('amount');

            // Bénéfice
            $benefice = DB::table('inventory_movements as im')
                ->join('invoices as i', 'im.invoice_id', '=', 'i.id')
                ->where('i.tenant_id', $tenant->id)
                ->where('im.reason', 'vente')
                ->whereBetween('im.created_at', [$start, $end])
                ->sum('im.profit');

            return [
                'start' => $start,
                'end' => $end,
                'invoices' => $invoiceStats,
                'counts' => $counts,
                'nbProduits' => $nbProduits,
                'nbEntrepots' => $nbEntrepots,
                'paiements' => $paiements,
                'balance_clients' => $balance_clients,
                'balance_fournisseurs' => $balance_fournisseurs,
                'benefice' => $benefice,
                'depenses' => $depenses,
            ];
        });

        // Dernières factures filtrées par la même période
        $dernieresFactures = DB::table('invoices as i')
            ->join('contacts as c', 'i.contact_id', '=', 'c.id')
            ->select('i.id', 'i.invoice_number', 'i.invoice_date', 'i.total_invoice',
                'c.id as contact_id', 'i.status',
                'c.type as contact_type', 'c.fullname as client')
            ->where('i.tenant_id', $tenant->id)
            ->whereBetween('i.invoice_date', [$start, $end])
            ->orderByDesc('i.invoice_date')
            ->limit(10)
            ->get();

        // Derniers paiements filtrés par la même période
        $derniersPaiements = DB::table('payments as p')
            ->join('contacts as c', 'p.contact_id', '=', 'c.id')
            ->select(
                'p.amount_paid',
                'p.payment_date',
                DB::raw('c.fullname as client'),
                'c.type as contact_type',
                'c.id as contact_id'
            )
            ->where('p.tenant_id', $tenant->id)
            ->whereBetween('p.payment_date', [$start, $end])
            ->orderByDesc('p.payment_date')
            ->limit(10)
            ->get();

        return view('back.dashboard.client', [
            'period' => $period,
            'stats' => $stats,
            'dernieresFactures' => $dernieresFactures,
            'derniersPaiements' => $derniersPaiements,
        ]);
    }
}
