<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $navigationGroup = 'Tienda';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Pedido')
                    ->schema([

                        Forms\Components\TextInput::make('order_number')
                            ->label('Número de pedido')
                            ->disabled(),

                        Forms\Components\TextInput::make('created_at')
                            ->label('Fecha')
                            ->formatStateUsing(
                                fn(Order $record): string =>
                                    $record->created_at->format('d/m/Y H:i')
                            )
                            ->disabled(),

                    ])
                    ->columns(2),


                Forms\Components\Section::make('Comprador')
                    ->schema([

                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nombre')
                            ->disabled(),

                        Forms\Components\TextInput::make('customer_email')
                            ->label('Correo')
                            ->disabled(),

                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Teléfono')
                            ->disabled(),

                    ])
                    ->columns(3),


                Forms\Components\Section::make('Entrega')
                    ->schema([

                        Forms\Components\TextInput::make('shipping_recipient')
                            ->label('Destinatario')
                            ->disabled(),

                        Forms\Components\TextInput::make('shipping_phone')
                            ->label('Teléfono')
                            ->disabled(),

                        Forms\Components\TextInput::make('shipping_department')
                            ->label('Departamento')
                            ->disabled(),

                        Forms\Components\TextInput::make('shipping_municipality')
                            ->label('Municipio')
                            ->disabled(),

                        Forms\Components\Textarea::make('shipping_address')
                            ->label('Dirección')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('shipping_references')
                            ->label('Referencias')
                            ->disabled()
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Totales')
                    ->schema([

                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('Q')
                            ->disabled(),

                        Forms\Components\TextInput::make('shipping_cost')
                            ->label('Envío')
                            ->prefix('Q')
                            ->disabled(),

                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->prefix('Q')
                            ->disabled(),

                    ])
                    ->columns(3),
                Forms\Components\Section::make('Gestión del pedido')
                    ->schema([

                        Forms\Components\Placeholder::make('status_display')
                            ->label('Estado del pedido')
                            ->content(function (?Order $record): string {
                                return match ($record?->status) {
                                    Order::STATUS_PENDING => 'Pendiente',
                                    Order::STATUS_PROCESSING => 'Procesando',
                                    Order::STATUS_SHIPPED => 'Enviado',
                                    Order::STATUS_COMPLETED => 'Completado',
                                    Order::STATUS_CANCELLED => 'Cancelado',
                                    default => '—',
                                };
                            }),

                        Forms\Components\Select::make('payment_status')
                            ->label('Estado del pago')
                            ->options([
                                'pending' => 'Pendiente',
                                'paid' => 'Pagado',
                                'failed' => 'Fallido',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('payment_method')
                            ->label('Método de pago')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('payment_url')
                            ->label('Link de pago')
                            ->url()
                            ->maxLength(2048)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notas internas')
                            ->rows(4)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),


                Forms\Components\Section::make('Notas del cliente')
                    ->schema([

                        Forms\Components\Textarea::make('customer_notes')
                            ->label('Notas')
                            ->disabled(),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Pedido')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->description(
                        fn(Order $record): string =>
                            $record->customer_email
                    ),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('GTQ')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(
                        fn(string $state): string =>
                            match ($state) {
                                'pending' => 'Pendiente',
                                'processing' => 'Procesando',
                                'shipped' => 'Enviado',
                                'completed' => 'Completado',
                                'cancelled' => 'Cancelado',
                                default => $state,
                            }
                    )
                    ->color(
                        fn(string $state): string =>
                            match ($state) {
                                'pending' => 'warning',
                                'processing' => 'info',
                                'shipped' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }
                    ),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(
                        fn(string $state): string =>
                            match ($state) {
                                'pending' => 'Pendiente',
                                'paid' => 'Pagado',
                                'failed' => 'Fallido',
                                'cancelled' => 'Cancelado',
                                default => $state,
                            }
                    )
                    ->color(
                        fn(string $state): string =>
                            match ($state) {
                                'pending' => 'warning',
                                'paid' => 'success',
                                'failed' => 'danger',
                                'cancelled' => 'gray',
                                default => 'gray',
                            }
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'shipped' => 'Enviado',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Pago')
                    ->options([
                        'pending' => 'Pendiente',
                        'paid' => 'Pagado',
                        'failed' => 'Fallido',
                        'cancelled' => 'Cancelado',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Gestionar'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
