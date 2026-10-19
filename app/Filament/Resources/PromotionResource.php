<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Models\Promotion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Tienda';
    protected static ?string $modelLabel = 'Promoción';
    protected static ?string $pluralModelLabel = 'Promociones';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(255),
            Forms\Components\Hidden::make('type')->default('category_bundle'),
            Forms\Components\Select::make('category_id')->label('Categoría')->relationship('category', 'name')->required()->searchable()->preload(),
            Forms\Components\TextInput::make('eligible_price')->label('Precio normal elegible')->prefix('Q')->numeric()->minValue(0.01)->step(0.01)->rules(['decimal:0,2'])->required(),
            Forms\Components\TextInput::make('bundle_quantity')->label('Unidades por paquete')->numeric()->integer()->minValue(2)->required()->default(2),
            Forms\Components\TextInput::make('bundle_price')->label('Precio del paquete')->prefix('Q')->numeric()->minValue(0)->step(0.01)->rules(['decimal:0,2'])->required(),
            Forms\Components\DateTimePicker::make('starts_at')->label('Inicio')->helperText('Horario de la aplicación: ' . config('app.timezone')),
            Forms\Components\DateTimePicker::make('ends_at')->label('Fin (exclusivo)')->after('starts_at'),
            Forms\Components\Toggle::make('is_active')->label('Activa')->default(false),
            Forms\Components\Placeholder::make('rules')->label('Aplicación')->content('Combina modelos de la misma categoría y precio. Repite paquetes completos; las unidades restantes conservan el precio normal. Se elige una sola promoción por grupo, la de mayor ahorro.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable(),
            Tables\Columns\TextColumn::make('category.name')->label('Categoría'),
            Tables\Columns\TextColumn::make('eligible_price')->label('Precio normal')->money('GTQ'),
            Tables\Columns\TextColumn::make('bundle_quantity')->label('Unidades'),
            Tables\Columns\TextColumn::make('bundle_price')->label('Paquete')->money('GTQ'),
            Tables\Columns\IconColumn::make('is_active')->label('Activa')->boolean(),
            Tables\Columns\TextColumn::make('starts_at')->label('Inicio')->dateTime(),
            Tables\Columns\TextColumn::make('ends_at')->label('Fin')->dateTime(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPromotions::route('/'), 'create' => Pages\CreatePromotion::route('/create'), 'edit' => Pages\EditPromotion::route('/{record}/edit')];
    }

    public static function canViewAny(): bool { return auth()->user()?->can('promociones.ver') ?? false; }
    public static function canCreate(): bool { return auth()->user()?->can('promociones.crear') ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->can('promociones.editar') ?? false; }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }
}
