<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    //
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $type = $request->input('type');

        $tenantId = auth()->user()->tenant_id;

        // Base Query optimisée
        $baseQuery = Invoice::query()
            ->where('tenant_id', $tenantId)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($dateFrom, fn ($q) => $q->whereDate('invoice_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('invoice_date', '<=', $dateTo));

        // Statistiques globales (1 seule requête SQL)
        $stats = $baseQuery->clone()->selectRaw("
        SUM(total_invoice) AS total_factures,
        SUM(total_invoice - balance) AS total_paye,
        SUM(balance) AS total_attente,
        SUM(CASE WHEN status = 'cancelled' THEN total_invoice ELSE 0 END) AS total_annule,
        COUNT(*) AS nb_ventes
    ")->first();

        // Dépenses
        $depenses = Expense::where('tenant_id', $tenantId)
            ->when($dateFrom, fn ($q) => $q->whereDate('expense_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('expense_date', '<=', $dateTo))
            ->sum('amount');

        $benefice = DB::table('inventory_movements as im')
            ->join('invoices as i', 'im.invoice_id', '=', 'i.id')
            ->where('i.tenant_id', $tenantId)
            ->where('im.reason', 'vente')
            ->when($dateFrom, fn ($q) => $q->whereDate('im.created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('im.created_at', '<=', $dateTo))
            ->sum('im.profit');

        $beneficeNet = $benefice - $depenses;
        $stats->benefice = $benefice;
        $stats->depenses = $depenses;
        $stats->benefice_net = $beneficeNet;

        $chartData = $baseQuery->clone()
            ->selectRaw('DATE(invoice_date) as date, SUM(total_invoice) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();


        $invoicesList = $baseQuery->clone()
            ->with('contact')
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

        $tenantId = auth()->user()->tenant_id;

        $itemsQuery = InvoiceItem::query()
            ->where('type', 'out')
            ->whereHas('invoice', fn ($q) => $q->where('tenant_id', $tenantId));

        $itemsQuery->with([
            'invoice:id,status,type,invoice_date,invoice_number',
            'product:id,name',
            'warehouse:id,name',
        ]);

        // Factures valides
        $itemsQuery->whereHas('invoice', function ($q) {
            $q->where('type', 'client')
                ->whereNotIn('status', ['draft', 'cancelled']);
        });

        if ($productId) {
            $itemsQuery->where('product_id', $productId);
        }

        if ($startDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $itemsQuery->where('created_at', '>=', $start);
        }

        if ($endDate) {
            $end = Carbon::parse($endDate)->endOfDay();
            $itemsQuery->where('created_at', '<=', $end);
        }

        if (empty($startDate) && empty($endDate) && empty($productId)) {
            $itemsQuery->limit(50);
        }

        $invoiceItems = $itemsQuery->get();

        $quantityInByProduct = InvoiceItem::where('type', 'in')
            ->whereHas('invoice', fn ($q) => $q->where('tenant_id', $tenantId))
            ->selectRaw('product_id, SUM(quantity) as total_in')
            ->groupBy('product_id')
            ->pluck('total_in', 'product_id');

        $reportData = $invoiceItems->map(function ($item) use ($quantityInByProduct) {

            $qtySold = (int) $item->quantity;
            $unitPrice = (int) $item->unit_price;
            $totalSale = (int) $item->total_line;

            $quantityIn = (int) ($quantityInByProduct[$item->product_id] ?? 0);

            $remaining = $quantityIn - $qtySold;

            $invoiceDate = optional($item->invoice)->invoice_date
                ? Carbon::parse($item->invoice->invoice_date)->format('d/m/Y')
                : optional($item->created_at)->format('d/m/Y');

            return [
                'item_id' => $item->id,
                'invoice_id' => optional($item->invoice)->id,
                'invoice_number' => optional($item->invoice)->invoice_number ?? 'N/A',
                'warehouse_name' => optional($item->warehouse)->name ?? 'N/A',
                'product_name' => optional($item->product)->name ?? 'N/A',
                'qty_sold' => $qtySold,
                'quantity_in' => $quantityIn,
                'remaining' => $remaining,
                'unit_price' => $unitPrice,
                'total_sale' => $totalSale,
                'date' => $invoiceDate ?? 'N/A',
            ];
        })->values();

        $totalRevenue = $reportData->sum('total_sale');
        $totalQtySold = $reportData->sum('qty_sold');

        $products = Product::where('tenant_id', $tenantId)->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        return view('back.reports.product', compact(
            'reportData',
            'totalRevenue',
            'totalQtySold',
            'startDate',
            'endDate',
            'products',
            'productId'
        ));
    }

    public function suppliers(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $supplierId = $request->input('supplier_id');

        // Liste des fournisseurs
        $suppliers = Contact::where('tenant_id', auth()->user()->tenant_id)
            ->where('type', 'supplier')->get();

        // Base Query
        $query = Payment::query()
            ->with([
                'invoice:id,invoice_number,total_invoice,contact_id',
                'invoice.contact:id,fullname',
            ])
            ->where('payment_source', 'supplier')
            ->where('tenant_id', auth()->user()->tenant_id); // Paiement fournisseur seulement

        // 🔥 Filtre fournisseur
        if (! empty($supplierId)) {
            $query->whereHas('invoice', function ($q) use ($supplierId) {
                $q->where('contact_id', $supplierId);
            });
        }

        // 🔥 Filtre dates
        if (! empty($startDate) && ! empty($endDate)) {
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }

        // Résultats paginés (10 par page)
        if (! empty($supplierId)) {
            $payments = $query->orderBy('payment_date', 'asc')->get();
        } else {
            $payments = $query->orderBy('payment_date', 'asc')->paginate(10);
        }

        // $totalsQuery = clone $query;
        // $totals = $totalsQuery->get();
        $totals = (clone $query)->get();
        // ✅ TOTAL PAYÉ
        $totalPaid = $totals->sum('amount_paid');
        // ✅ TOTAL DES FACTURES UNIQUES
        $totalInvoices = $totals
            ->unique('invoice_id')
            ->sum(fn ($p) => $p->invoice->total_invoice ?? 0);

        $solde = $totalInvoices - $totalPaid;

        return view('back.reports.supplier', compact(
            'payments',
            'suppliers',
            'supplierId',
            'startDate',
            'endDate',
            'totalPaid',
            'solde'
        ));
    }

    public function reportSuppliers(Request $request)
    {

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $productId = $request->input('product_id');
        $supplierId = $request->input('supplier_id');

        $itemsQuery = InvoiceItem::query()
            ->where('type', 'in')
            ->whereHas('invoice', fn ($q) => $q->where('tenant_id', auth()->user()->tenant_id));

        $itemsQuery->with([
            'invoice:id,status,type,invoice_date,invoice_number,contact_id',
            'invoice.contact:id,fullname',
            'product:id,name',
            'warehouse:id,name',
        ]);

        $itemsQuery->whereHas('invoice', function ($q) use ($supplierId, $startDate, $endDate) {

            $q->where('type', 'supplier')
                ->whereNotIn('status', ['draft', 'cancelled']);

            if ($supplierId) {
                $q->where('contact_id', $supplierId);
            }

            if ($startDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $q->where('invoice_date', '>=', $start);
            }

            if ($endDate) {
                $end = Carbon::parse($endDate)->endOfDay();
                $q->where('invoice_date', '<=', $end);
            }
        });

        if ($productId) {
            $itemsQuery->where('product_id', $productId);
        }

        if (empty($startDate) && empty($endDate) && empty($productId) && empty($supplierId)) {
            $itemsQuery->limit(50);
        }

        $invoiceItems = $itemsQuery->get();

        $reportData = $invoiceItems->map(function ($item) {

            $qtyEntered = (int) $item->quantity;
            $unitPrice = (int) $item->unit_price;
            $totalLine = (int) $item->total_line;

            $invoiceDate = optional($item->invoice)->invoice_date
                ? Carbon::parse($item->invoice->invoice_date)->format('d/m/Y')
                : optional($item->created_at)->format('d/m/Y');

            $supplierName = optional(optional($item->invoice)->contact)->fullname ?? 'N/A';

            return [
                'item_id' => $item->id,
                'invoice_id' => optional($item->invoice)->id,
                'invoice_number' => optional($item->invoice)->invoice_number ?? 'N/A',
                'warehouse_name' => optional($item->warehouse)->name ?? 'N/A',
                'product_name' => optional($item->product)->name ?? 'N/A',
                'supplier_name' => $supplierName,
                'qty_entered' => $qtyEntered,
                'unit_price' => $unitPrice,
                'total_line' => $totalLine,
                'date' => $invoiceDate ?? 'N/A',
            ];
        })->values();

        $totalRevenue = $reportData->sum('total_line');
        $totalQtySold = $reportData->sum('qty_entered');

        $products = Product::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
        $suppliers = Contact::where('type', 'supplier')->get();

        return view('back.reports.reportSuppliers', compact(
            'reportData',
            'totalRevenue',
            'totalQtySold',
            'startDate',
            'endDate',
            'products',
            'suppliers',
            'supplierId',
            'productId'
        ));
    }
}
