<?php

namespace App\Http\Controllers;

use App\Models\CustomerCreditNote;
use App\Models\CustomerReturn;
use App\Models\SupplierCreditNote;
use App\Models\SupplierReturn;
use Illuminate\Http\Request;

class ReturnProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $customerReturns = CustomerReturn::query()->where('tenant_id', $tenantId);
        $supplierReturns = SupplierReturn::query()->where('tenant_id', $tenantId);
        $customerCredits = CustomerCreditNote::query()->where('tenant_id', $tenantId);
        $supplierCredits = SupplierCreditNote::query()->where('tenant_id', $tenantId);

        return view('back.returns.dashboard', [
            'title' => 'Tableau de bord retours / avoirs',
            'summary' => [
                'customer_returns' => $customerReturns->count(),
                'supplier_returns' => $supplierReturns->count(),
                'customer_credits' => $customerCredits->count(),
                'supplier_credits' => $supplierCredits->count(),
                'customer_credit_value' => (int) $customerCredits->sum('total_ttc'),
                'supplier_credit_value' => (int) $supplierCredits->sum('total_ttc'),
                'validated_returns' => CustomerReturn::query()->where('tenant_id', $tenantId)->where('status', 'validated')->count()
                    + SupplierReturn::query()->where('tenant_id', $tenantId)->where('status', 'validated')->count(),
                'draft_returns' => CustomerReturn::query()->where('tenant_id', $tenantId)->where('status', 'draft')->count()
                    + SupplierReturn::query()->where('tenant_id', $tenantId)->where('status', 'draft')->count(),
            ],
            'recentCustomerReturns' => CustomerReturn::query()
                ->where('tenant_id', $tenantId)
                ->with(['contact', 'invoice', 'creditNote'])
                ->latest()
                ->limit(5)
                ->get(),
            'recentSupplierReturns' => SupplierReturn::query()
                ->where('tenant_id', $tenantId)
                ->with(['contact', 'supplierInvoice', 'creditNote'])
                ->latest()
                ->limit(5)
                ->get(),
            'recentCustomerCredits' => CustomerCreditNote::query()
                ->where('tenant_id', $tenantId)
                ->with(['contact', 'invoice', 'customerReturn'])
                ->latest()
                ->limit(5)
                ->get(),
            'recentSupplierCredits' => SupplierCreditNote::query()
                ->where('tenant_id', $tenantId)
                ->with(['contact', 'invoice', 'supplierReturn'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ReturnProduct $returnProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReturnProduct $returnProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReturnProduct $returnProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReturnProduct $returnProduct)
    {
        //
    }
}
