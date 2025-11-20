<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function createInventory()
    {
        try {

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
