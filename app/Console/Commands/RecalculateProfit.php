<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryMovement;

class RecalculateProfit extends Command
{
    protected $signature = 'profit:recalculate';
    protected $description = 'Recalculer le profit de tous les mouvements de vente existants';

    public function handle()
    {
        $movements = InventoryMovement::where('reason', 'vente')->get();

        foreach ($movements as $movement) {
            $purchasePrice = $movement->batch->unit_price;
            $salePrice = $movement->invoiceItem->unit_price;
            $quantity = $movement->quantity;

            $movement->profit = ($salePrice - $purchasePrice) * $quantity;
            $movement->save();
        }

        $this->info('Profit recalculé pour tous les mouvements existants.');
    }
}
