<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?string $navigationLabel = 'Roles y permisos';

    protected static ?string $modelLabel = 'Rol';

    protected static ?string $pluralModelLabel = 'Roles y permisos';

    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del rol')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->disabled(
                                fn(?Role $record): bool =>
                                    $record !== null &&
                                    in_array(
                                        $record->name,
                                        ['Administrador', 'Cliente'],
                                        true
                                    )
                            )
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn($rule) =>
                                    $rule->where('guard_name', 'web')
                            ),

                        Forms\Components\Hidden::make('guard_name')
                            ->default('web'),
                    ]),

                Forms\Components\Section::make('Permisos')
                    ->description(
                        'Selecciona las acciones que podrán realizar los usuarios que tengan este rol.'
                    )
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('')
                            ->options(
                                \Spatie\Permission\Models\Permission::query()
                                    ->where('guard_name', 'web')
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(function ($permission) {

                                        [$module, $action] = array_pad(
                                            explode('.', $permission->name, 2),
                                            2,
                                            ''
                                        );

                                        $actions = [
                                            'ver' => 'Ver',
                                            'crear' => 'Crear',
                                            'editar' => 'Editar',
                                            'eliminar' => 'Eliminar',
                                            'procesar' => 'Procesar',
                                            'cancelar' => 'Cancelar',
                                            'gestionar' => 'Gestionar',
                                            'acceder' => 'Acceder',
                                        ];

                                        $label = sprintf(
                                            '%s — %s',
                                            ucfirst($module),
                                            $actions[$action] ?? ucfirst($action)
                                        );

                                        return [
                                            $permission->id => $label,
                                        ];
                                    })
                                    ->toArray()
                            )
                            ->columns(2)
                            ->bulkToggleable()
                            ->searchable()
                            ->disabled(
                                fn(?Role $record): bool =>
                                    $record?->name === 'Administrador'
                            )
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Rol')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permisos')
                    ->counts('permissions')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->visible(
                        fn(Role $record): bool =>
                            !in_array(
                                $record->name,
                                ['Administrador', 'Cliente'],
                                true
                            )
                    ),
            ])
            ->bulkActions([]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(
            'roles.gestionar'
        ) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(
            'roles.gestionar'
        ) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can(
            'roles.gestionar'
        ) ?? false;
    }

    public static function canDelete($record): bool
    {
        return
            (auth()->user()?->can('roles.gestionar') ?? false)
            && !in_array(
                $record->name,
                ['Administrador', 'Cliente'],
                true
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}