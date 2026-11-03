<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Product;
use App\Models\User;
use App\Support\GuatemalaLocations;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Order;
use App\Services\AdminOrderCreationService;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Tipo de comprador')
                ->schema([

                    Forms\Components\Radio::make('customer_type')
                        ->label('Comprador')
                        ->options([
                            'registered' => 'Cliente registrado',
                            'guest' => 'Comprador invitado',
                        ])
                        ->default('registered')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set) {
                            $set('user_id', null);
                            $set('customer_name', '');
                            $set('customer_email', '');
                            $set('customer_phone', '');
                            $set('shipping_recipient', '');
                            $set('shipping_phone', '');
                            $set('shipping_department', null);
                            $set('shipping_municipality', null);
                            $set('shipping_address', '');
                            $set('shipping_references', '');
                        }),

                    Forms\Components\Select::make('user_id')
                        ->label('Seleccionar cliente')
                        ->searchable()
                        ->getSearchResultsUsing(
                            fn(string $search): array =>
                                User::query()
                                    ->where(function ($query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->all()
                        )
                        ->getOptionLabelUsing(
                            fn($value): ?string =>
                                User::find($value)?->name
                        )
                        ->required(
                            fn(Forms\Get $get): bool =>
                                $get('customer_type') === 'registered'
                        )
                        ->visible(
                            fn(Forms\Get $get): bool =>
                                $get('customer_type') === 'registered'
                        )
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {

                            $user = User::with('addresses')
                                ->find($state);

                            if (!$user) {
                                return;
                            }

                            $set('customer_name', $user->name);
                            $set('customer_email', $user->email);
                            $set('customer_phone', $user->addresses()->where('is_default', true)->first()?->phone ?? '');

                            $set('shipping_recipient', $user->name);

                            $address = $user->addresses
                                ->firstWhere('is_default', true);

                            if (!$address) {
                                return;
                            }

                            $set('shipping_recipient', $address->recipient_name);
                            $set('shipping_phone', $address->phone);
                            $set('shipping_department', $address->department);
                            $set('shipping_municipality', $address->municipality);
                            $set('shipping_address', $address->address);
                            $set('shipping_references', $address->references);
                        }),

                ]),

            Forms\Components\Section::make('Datos del comprador')
                ->schema([

                    Forms\Components\TextInput::make('customer_name')
                        ->label('Nombre completo')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('customer_email')
                        ->label('Correo electrónico')
                        ->email()
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('customer_phone')
                        ->label('Teléfono')
                        ->tel()
                        ->required()
                        ->maxLength(30),

                ])
                ->columns(2),

            Forms\Components\Section::make('Dirección de entrega')
                ->schema([

                    Forms\Components\TextInput::make('shipping_recipient')
                        ->label('Nombre del destinatario')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('shipping_phone')
                        ->label('Teléfono de entrega')
                        ->tel()
                        ->required()
                        ->maxLength(30),

                    Forms\Components\Select::make('shipping_department')
                        ->label('Departamento')
                        ->options(GuatemalaLocations::departments())
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(
                            fn(Forms\Set $set) =>
                                $set('shipping_municipality', null)
                        ),

                    Forms\Components\Select::make('shipping_municipality')
                        ->label('Municipio')
                        ->options(
                            fn(Forms\Get $get): array =>
                                GuatemalaLocations::municipalitiesFor(
                                    $get('shipping_department')
                                )
                        )
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('shipping_address')
                        ->label('Dirección exacta')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('shipping_references')
                        ->label('Referencias')
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),

                ])
                ->columns(2),


            Forms\Components\Section::make('Productos del pedido')
                ->description('Selecciona los productos y las cantidades que deseas vender.')
                ->schema([

                    Forms\Components\Repeater::make('items')
                        ->label('Productos')
                        ->schema([

                            Forms\Components\Select::make('product_id')
                                ->label('Producto')
                                ->searchable()
                                ->getSearchResultsUsing(
                                    fn(string $search): array =>
                                        Product::query()
                                            ->where('is_active', true)
                                            ->where('stock', '>', 0)
                                            ->where(function ($query) use ($search) {
                                                $query->where('name', 'like', "%{$search}%")
                                                    ->orWhere('sku', 'like', "%{$search}%");
                                            })
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(fn(Product $product) => [
                                                $product->id =>
                                                    "{$product->name} ({$product->sku})",
                                            ])
                                            ->all()
                                )
                                ->getOptionLabelUsing(
                                    fn($value): ?string =>
                                        Product::find($value)?->name
                                )
                                ->required()
                                ->distinct()
                                ->live()
                                ->afterStateUpdated(
                                    function ($state, Forms\Set $set) {
                                        $product = Product::find($state);

                                        $set(
                                            'unit_price',
                                            $product?->price ?? '0.00'
                                        );
                                    }
                                )
                                ->columnSpan(5),

                            Forms\Components\TextInput::make('quantity')
                                ->label('Cantidad')
                                ->numeric()
                                ->integer()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->live()
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('unit_price')
                                ->label('Precio unitario')
                                ->prefix('Q')
                                ->readOnly()
                                ->dehydrated(false)
                                ->default('0.00')
                                ->columnSpan(2),

                            Forms\Components\Placeholder::make('line_subtotal')
                                ->label('Subtotal')
                                ->content(
                                    fn(Forms\Get $get): string =>
                                        'Q ' . number_format(
                                            (float) ($get('unit_price') ?? 0)
                                            * max(0, (int) ($get('quantity') ?? 0)),
                                            2
                                        )
                                )
                                ->columnSpan(3),

                        ])
                        ->columnSpan(12)
                        ->defaultItems(1)
                        ->minItems(1)
                        ->addActionLabel('Agregar otro producto')
                        ->reorderable(false)
                        ->collapsible()
                        ->required(),


                    Forms\Components\Placeholder::make('order_subtotal')
                        ->label('Subtotal del pedido')
                        ->content(function (Forms\Get $get): string {

                            $items = $get('items') ?? [];

                            $productIds = collect($items)
                                ->pluck('product_id')
                                ->filter()
                                ->unique()
                                ->values()
                                ->all();

                            $products = Product::query()
                                ->whereIn('id', $productIds)
                                ->get()
                                ->keyBy('id');

                            $subtotal = collect($items)->sum(function ($item) use ($products) {

                                $product = $products->get($item['product_id'] ?? null);

                                if (!$product) {
                                    return 0;
                                }

                                $quantity = max(0, (int) ($item['quantity'] ?? 0));

                                return round((float) $product->price * $quantity, 2);
                            });

                            return 'Q ' . number_format($subtotal, 2);
                        }),

                    Forms\Components\Placeholder::make('order_total')
                        ->label('Total del pedido')
                        ->content(function (Forms\Get $get): string {

                            $items = $get('items') ?? [];

                            $productIds = collect($items)
                                ->pluck('product_id')
                                ->filter()
                                ->unique()
                                ->values()
                                ->all();

                            $products = Product::query()
                                ->whereIn('id', $productIds)
                                ->get()
                                ->keyBy('id');

                            $total = collect($items)->sum(function ($item) use ($products) {

                                $product = $products->get($item['product_id'] ?? null);

                                if (!$product) {
                                    return 0;
                                }

                                $quantity = max(0, (int) ($item['quantity'] ?? 0));

                                return round((float) $product->price * $quantity, 2);
                            });


                            $discountType = $get('discount_type');
                            $discountValue = max(
                                0,
                                (float) ($get('discount_value') ?? 0)
                            );

                            if (
                                auth()->user()?->can('pedidos.aplicar-descuento')
                                && $discountType === 'percent'
                            ) {
                                $discount = round(
                                    $total * min($discountValue, 100) / 100,
                                    2
                                );
                            } elseif (
                                auth()->user()?->can('pedidos.aplicar-descuento')
                                && $discountType === 'fixed'
                            ) {
                                $discount = min($total, $discountValue);
                            } else {
                                $discount = 0;
                            }

                            $total = max(0, round($total - $discount, 2));


                            return 'Q ' . number_format($total, 2);
                        }),


                ])
                ->columns(2),


            Forms\Components\Section::make('Descuento manual')
                ->description('Aplica un descuento especial al pedido.')
                ->visible(
                    fn(): bool =>
                        auth()->user()?->can('pedidos.aplicar-descuento') ?? false
                )
                ->schema([

                    Forms\Components\Select::make('discount_type')
                        ->label('Tipo de descuento')
                        ->options([
                            'fixed' => 'Monto fijo (Q)',
                            'percent' => 'Porcentaje (%)',
                        ])
                        ->placeholder('Sin descuento')
                        ->live()
                        ->afterStateUpdated(
                            fn(Forms\Set $set) =>
                                $set('discount_value', null)
                        ),

                    Forms\Components\TextInput::make('discount_value')
                        ->label('Valor del descuento')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(
                            fn(Forms\Get $get) =>
                                $get('discount_type') === 'percent'
                                ? 100
                                : null
                        )
                        ->step(0.01)
                        ->live()
                        ->disabled(
                            fn(Forms\Get $get) =>
                                blank($get('discount_type'))
                        )
                        ->required(
                            fn(Forms\Get $get) =>
                                filled($get('discount_type'))
                        ),

                    Forms\Components\Textarea::make('discount_reason')
                        ->label('Motivo del descuento')
                        ->placeholder('Ej. Descuento especial autorizado')
                        ->rows(2)
                        ->maxLength(1000)
                        ->required(
                            fn(Forms\Get $get) =>
                                filled($get('discount_type'))
                                && (float) $get('discount_value') > 0
                        )
                        ->columnSpanFull(),

                ])
                ->columns(2),


            Forms\Components\Section::make('Observaciones')
                ->schema([

                    Forms\Components\Textarea::make('customer_notes')
                        ->label('Notas del pedido')
                        ->rows(3)
                        ->maxLength(1000),

                ]),

        ]);
    }



    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(AdminOrderCreationService::class)
                ->create($data);

        } catch (ValidationException $e) {

            Notification::make()
                ->title('No se pudo crear el pedido')
                ->body(
                    collect($e->errors())
                        ->flatten()
                        ->first() ?? 'Verifica los datos del pedido.'
                )
                ->danger()
                ->persistent()
                ->send();

            throw $e;
        }
    }


    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pedido creado correctamente';
    }

    protected function getRedirectUrl(): string
    {
        return OrderResource::getUrl('edit', [
            'record' => $this->record,
        ]);
    }

}
