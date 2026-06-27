<?php

namespace App\Http\Controllers;

use App\Models\ReturnProduct;
use Illuminate\Http\Request;

class ReturnProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $returnProducts = \App\Models\ReturnProduct::paginate(15);
        return view('back.return_products.index', compact('returnProducts'));
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
