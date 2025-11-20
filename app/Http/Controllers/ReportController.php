<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Invoice;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    //

    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $type = $request->input('type');

        // ✅ Base Query optimisée
        $baseQuery = Invoice::query()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($dateFrom, fn ($q) => $q->whereDate('invoice_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('invoice_date', '<=', $dateTo));

        // ✅ Statistiques globales (1 seule requête SQL)
        $stats = $baseQuery->clone()->selectRaw("
            SUM(total_invoice) AS total_factures,
            SUM(total_invoice - balance) AS total_paye,
            SUM(balance) AS total_attente,
            SUM(CASE WHEN status = 'cancelled' THEN total_invoice ELSE 0 END) AS total_annule
        ")->first();

        // ✅ Graphique (évolution par date)
        $chartData = $baseQuery->clone()
            ->selectRaw('DATE(invoice_date) as date, SUM(total_invoice) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // ✅ Liste détaillée des factures
        $invoicesList = $baseQuery->clone()
            ->with('contact') // relation contact = client / fournisseur
            ->orderBy('invoice_date', 'DESC')
            ->paginate(10);

        return view('back.reports.index', compact('stats', 'chartData', 'invoicesList'));
    }

    public function journal()
    {
        return view('back.reports.journal');
    }

    public function products(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $productId = $request->input('product_id');

        // Charger relations utiles
        $batchesQuery = Batch::with(['product', 'warehouse', 'invoice']);

        // Filtrer par date de la facture si fourni
        if ($startDate) {
            $batchesQuery->whereHas('invoice', function ($q) use ($startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            });
        }

        if ($endDate) {
            $batchesQuery->whereHas('invoice', function ($q) use ($endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            });
        }

        if(empty($startDate) && empty($endDate)){
            $batchesQuery->limit(10);
        }

        // Filtrer par produit si sélectionné
        if ($productId) {
            $batchesQuery->where('product_id', $productId);
        }

        $batches = $batchesQuery->get();

        // Charger tous les produits pour le select
        $products = Product::where('is_active', true)->orderBy('name','asc')->get();

        // Construire les lignes du rapport
        $reportData = $batches->map(function ($batch) {
            $quantityIn = (int) ($batch->quantity ?? 0);
            $remaining = (int) ($batch->remaining ?? 0);
            $qtySold = max(0, $quantityIn - $remaining); 

            $unitPrice = (int) ($batch->unit_price ?? 0);
            $totalSale = $qtySold * $unitPrice;

            return [
                'batch_id' => $batch->id,
                'warehouse_name' => optional($batch->warehouse)->name ?? 'N/A',
                'product_name' => optional($batch->product)->name ?? 'N/A',
                'quantity_in' => $quantityIn,
                'remaining' => $remaining,
                'qty_sold' => $qtySold,
                'unit_price' => $unitPrice,
                'total_sale' => $totalSale,
                'date' => optional($batch->invoice)->created_at
                            ? Carbon::parse($batch->invoice->created_at)->format('d/m/Y')
                            : ($batch->created_at ? Carbon::parse($batch->created_at)->format('d/m/Y') : 'N/A'),
            ];
        })
            ->filter(fn ($row) => $row['qty_sold'] > 0)
            ->values();

        // Totaux
        $totalRevenue = $reportData->sum('total_sale');
        $totalQtySold = $reportData->sum('qty_sold');

        return view('back.reports.product', compact(
            'reportData',
            'totalRevenue',
            'totalQtySold',
            'startDate',
            'endDate',
            'products',
            'productId' // important pour garder le select actif
        ));
    }

    public function suppliers()
    {
        return view('back.reports.supplier');
    }
}
