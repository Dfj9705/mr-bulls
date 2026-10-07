<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Filament\Resources\InventoryMovementResource\RelationManagers;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryMovementResource extends Resource
{
    protected static ?string $model = InventoryMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Tienda';

    protected static ?string $navigationLabel = 'Movimientos de inventario';

    protected static ?string $modelLabel = 'Movimiento de inventario';

    protected static ?string $pluralModelLabel = 'Movimientos de inventario';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable(),

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
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'sale' => 'info',
                        'cancellation' => 'warning',
                        'entry' => 'success',
                        'adjustment_positive' => 'success',
                        'adjustment_negative' => 'danger',
                        default => 'gray',
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
                    ->placeholder('—')
                    ->searchable()
                    ->url(
                        fn($record): ?string =>
                            $record->order
                            ? \App\Filament\Resources\OrderResource::getUrl(
                                'edit',
                                ['record' => $record->order]
                            )
                            : null
                    )
                    ->openUrlInNewTab(false),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsable')
                    ->placeholder('Sistema'),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->reason),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo de movimiento')
                    ->options([
                        'sale' => 'Venta',
                        'cancellation' => 'Cancelación',
                        'entry' => 'Entrada',
                        'adjustment_positive' => 'Ajuste positivo',
                        'adjustment_negative' => 'Ajuste negativo',
                    ]),

                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Responsable')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryMovements::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('inventario.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
