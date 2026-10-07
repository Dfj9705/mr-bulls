<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\InventoryMovement;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $stock = (int) $this->record->stock;

        if ($stock <= 0) {
            return;
        }

        InventoryMovement::create([
            'product_id' => $this->record->id,
            'order_id' => null,
            'user_id' => auth()->id(),
            'type' => 'entry',
            'quantity' => $stock,
            'stock_before' => 0,
            'stock_after' => $stock,
            'reason' => 'Stock inicial del producto.',
        ]);
    }
}
