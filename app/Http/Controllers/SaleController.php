<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SaleController extends Controller
{
    //
    public function index() {
        return view("back.sales.index");
    }

    public function store() {
        return dd('ok');
    }
}
