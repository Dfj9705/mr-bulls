<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Support\GuatemalaLocations;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->label('Nombre de la dirección')
                    ->placeholder('Casa, Oficina...')
                    ->maxLength(255),

                Forms\Components\TextInput::make('recipient_name')
                    ->label('Nombre de quien recibe')
                    ->required(),

                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->required(),

                Forms\Components\Select::make('department')
                    ->label('Departamento')
                    ->options(GuatemalaLocations::departments())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('municipality', null);
                    })
                    ->required(),

                Forms\Components\Select::make('municipality')
                    ->label('Municipio')
                    ->options(
                        fn(Get $get): array =>
                            GuatemalaLocations::municipalitiesFor($get('department'))
                    )
                    ->searchable()
                    ->preload()
                    ->disabled(fn(Get $get): bool => blank($get('department')))
                    ->required(),

                Forms\Components\TextInput::make('address')
                    ->label('Dirección')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('references')
                    ->label('Referencias')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_default')
                    ->label('Dirección predeterminada')
                    ->default(false),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Nombre'),

                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Destinatario'),

                Tables\Columns\TextColumn::make('department')
                    ->label('Departamento'),

                Tables\Columns\TextColumn::make('municipality')
                    ->label('Municipio'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono'),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Principal')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Agregar dirección'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
