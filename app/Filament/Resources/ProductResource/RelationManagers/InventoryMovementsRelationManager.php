<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryMovements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Tipo de movimiento')
                    ->options([
                        'entry' => 'Entrada de inventario',
                        'adjustment_positive' => 'Ajuste positivo',
                        'adjustment_negative' => 'Ajuste negativo',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->required(),

                Forms\Components\Textarea::make('reason')
                    ->label('Motivo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(
                fn(): string =>
                    'Movimientos de inventario — Stock actual: ' .
                    $this->getOwnerRecord()->fresh()->stock
            )
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Movimiento')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sale' => 'Venta',
                        'cancellation' => 'Cancelación',
                        'entry' => 'Entrada',
                        'adjustment_positive' => 'Ajuste +',
                        'adjustment_negative' => 'Ajuste -',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->formatStateUsing(
                        fn($state) => $state > 0 ? "+{$state}" : $state
                    ),

                Tables\Columns\TextColumn::make('stock_before')
                    ->label('Anterior'),

                Tables\Columns\TextColumn::make('stock_after')
                    ->label('Nuevo'),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Pedido')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('Sistema'),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->reason),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar movimiento')
                    ->icon('heroicon-o-plus')
                    ->visible(
                        fn() => auth()->user()?->can('inventario.ajustar')
                    )
                    ->using(function (array $data) {

                        return DB::transaction(function () use ($data) {

                            $product = Product::query()
                                ->lockForUpdate()
                                ->findOrFail($this->getOwnerRecord()->id);

                            $quantity = (int) $data['quantity'];
                            $stockBefore = $product->stock;

                            $signedQuantity = match ($data['type']) {
                                'entry',
                                'adjustment_positive' => $quantity,

                                'adjustment_negative' => -$quantity,
                            };

                            $stockAfter = $stockBefore + $signedQuantity;

                            if ($stockAfter < 0) {
                                throw ValidationException::withMessages([
                                    'quantity' => 'El movimiento dejaría el inventario en negativo.',
                                ]);
                            }

                            $product->update([
                                'stock' => $stockAfter,
                            ]);

                            $movement = InventoryMovement::create([
                                'product_id' => $product->id,
                                'order_id' => null,
                                'user_id' => auth()->id(),
                                'type' => $data['type'],
                                'quantity' => $signedQuantity,
                                'stock_before' => $stockBefore,
                                'stock_after' => $stockAfter,
                                'reason' => $data['reason'],
                            ]);



                            return $movement;


                        });
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
