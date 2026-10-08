<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductCommentResource\Pages;
use App\Models\ProductComment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductCommentResource extends Resource
{
    protected static ?string $model = ProductComment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Comentarios';

    protected static ?string $modelLabel = 'Comentario';

    protected static ?string $pluralModelLabel = 'Comentarios';

    protected static ?string $navigationGroup = 'Tienda';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('product.name')
                ->label('Producto')
                ->disabled()
                ->dehydrated(false),

            Forms\Components\TextInput::make('user.name')
                ->label('Cliente')
                ->disabled()
                ->dehydrated(false),

            Forms\Components\Textarea::make('content')
                ->label('Comentario')
                ->rows(5)
                ->disabled()
                ->dehydrated(false)
                ->columnSpanFull(),

            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    ProductComment::STATUS_PENDING => 'Pendiente',
                    ProductComment::STATUS_APPROVED => 'Aprobado',
                    ProductComment::STATUS_REJECTED => 'Rechazado',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn(Builder $query) =>
                    $query->with(['product', 'user'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('content')
                    ->label('Comentario')
                    ->limit(70)
                    ->wrap()
                    ->tooltip(fn(ProductComment $record) => $record->content),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        default => $state,
                    })
                    ->color(fn(string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

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
                        'pending' => 'Pendientes',
                        'approved' => 'Aprobados',
                        'rejected' => 'Rechazados',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(
                        fn(ProductComment $record) =>
                            $record->status !== ProductComment::STATUS_APPROVED
                            && auth()->user()?->can('comentarios.moderar')
                    )
                    ->requiresConfirmation()
                    ->action(
                        fn(ProductComment $record) =>
                            $record->forceFill([
                                'status' => ProductComment::STATUS_APPROVED,
                            ])->save()
                    ),

                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(
                        fn(ProductComment $record) =>
                            $record->status !== ProductComment::STATUS_REJECTED
                            && auth()->user()?->can('comentarios.moderar')
                    )
                    ->requiresConfirmation()
                    ->action(
                        fn(ProductComment $record) =>
                            $record->forceFill([
                                'status' => ProductComment::STATUS_REJECTED,
                            ])->save()
                    ),

                Tables\Actions\DeleteAction::make()
                    ->visible(
                        fn() =>
                            auth()->user()?->can('comentarios.eliminar')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(
                            fn() =>
                                auth()->user()?->can('comentarios.eliminar')
                        ),
                ]),
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('comentarios.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('comentarios.moderar') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('comentarios.eliminar') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('comentarios.eliminar') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductComments::route('/'),
        ];
    }
}
