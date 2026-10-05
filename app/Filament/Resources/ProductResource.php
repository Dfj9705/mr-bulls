<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    protected static ?string $navigationGroup = 'Tienda';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del producto')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, Forms\Set $set) =>
                                    $set('slug', Str::slug($state))
                            ),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),

                        Forms\Components\Textarea::make('short_description')
                            ->label('Descripción corta')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Precio e inventario')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Precio')
                            ->prefix('Q')
                            ->numeric()
                            ->required()
                            ->minValue(0),

                        Forms\Components\TextInput::make('compare_price')
                            ->label('Precio anterior')
                            ->prefix('Q')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Opcional. Se utilizará para mostrar ofertas.'),

                        Forms\Components\TextInput::make('stock')
                            ->label('Existencias')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Imagen y publicación')
                    ->schema([
                        Forms\Components\FileUpload::make('main_image')
                            ->label('Imagen principal')
                            ->image()
                            ->directory('products')
                            ->imageEditor(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Producto activo')
                            ->default(true),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Producto destacado')
                            ->default(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Galería del producto')
                    ->description('Agrega imágenes adicionales que se mostrarán en el detalle del producto.')
                    ->schema([
                        Forms\Components\Repeater::make('images')
                            ->label('Imágenes')
                            ->relationship()
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('Imagen')
                                    ->image()
                                    ->directory('products/gallery')
                                    ->imageEditor()
                                    ->required(),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Agregar imagen')
                            ->reorderable('sort_order')
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Imagen')
                    ->square(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('GTQ')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos')
                    ->placeholder('Todos'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Destacados')
                    ->trueLabel('Destacados')
                    ->falseLabel('No destacados')
                    ->placeholder('Todos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
