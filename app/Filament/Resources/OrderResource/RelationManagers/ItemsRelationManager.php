<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([

                Tables\Columns\TextColumn::make('product_name')
                    ->label('Producto')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('product_sku')
                    ->label('SKU'),

                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Precio efectivo promedio')
                    ->money('GTQ'),

                Tables\Columns\TextColumn::make('base_unit_price')
                    ->label('Precio normal')
                    ->money('GTQ'),

                Tables\Columns\TextColumn::make('promotion_discount')
                    ->label('Ahorro en la línea')
                    ->money('GTQ'),

                Tables\Columns\TextColumn::make('promotion_snapshot.name')
                    ->label('Promoción'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad'),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('GTQ'),

            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
