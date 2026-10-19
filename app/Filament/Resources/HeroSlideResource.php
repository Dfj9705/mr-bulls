<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Tienda';
    protected static ?string $navigationLabel = 'Hero de inicio';
    protected static ?string $modelLabel = 'Diapositiva';
    protected static ?string $pluralModelLabel = 'Diapositivas del hero';

    public static function form(Form $form): Form
    {
        $linkRule = fn () => function (string $attribute, $value, \Closure $fail) {
            if (!HeroSlide::validLink($value)) { $fail('Usa una ruta /productos, un ancla #productos-destacados o una URL http/https.'); }
        };
        return $form->schema([
            Forms\Components\Section::make('Imagen')->schema([
                Forms\Components\FileUpload::make('image')->label('Imagen')->disk('public')->visibility('public')->directory('hero')
                    ->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)->imageEditor()->required()
                    ->helperText('PNG, JPG o WebP, hasta 5 MB. Usa imágenes de proporciones similares para mantener la altura del carrusel.'),
                Forms\Components\TextInput::make('image_alt')->label('Descripción de la imagen')->required()->maxLength(180),
            ])->columns(2),
            Forms\Components\Section::make('Contenido')->schema([
                Forms\Components\TextInput::make('eyebrow')->label('Texto superior')->maxLength(80),
                Forms\Components\TextInput::make('title')->label('Título')->required()->maxLength(120),
                Forms\Components\TextInput::make('highlight')->label('Texto destacado del título')->maxLength(120),
                Forms\Components\Textarea::make('description')->label('Descripción')->maxLength(1000)->rows(3),
                Forms\Components\TextInput::make('brand_line')->label('Frase de marca')->maxLength(180),
            ])->columns(2),
            Forms\Components\Section::make('Botones opcionales')->schema([
                Forms\Components\TextInput::make('primary_label')->label('Texto del botón principal')->maxLength(80)->requiredWith('primary_url'),
                Forms\Components\TextInput::make('primary_url')->label('Enlace principal')->maxLength(500)->requiredWith('primary_label')->rules([$linkRule]),
                Forms\Components\TextInput::make('secondary_label')->label('Texto del botón secundario')->maxLength(80)->requiredWith('secondary_url'),
                Forms\Components\TextInput::make('secondary_url')->label('Enlace secundario')->maxLength(500)->requiredWith('secondary_label')->rules([$linkRule]),
            ])->columns(2),
            Forms\Components\Section::make('Publicación')->schema([
                Forms\Components\TextInput::make('sort_order')->label('Orden')->numeric()->integer()->minValue(0)->default(0)->required(),
                Forms\Components\Toggle::make('is_active')->label('Visible en inicio')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->label('Imagen')->disk('public'),
            Tables\Columns\TextColumn::make('title')->label('Título')->searchable(),
            Tables\Columns\TextColumn::make('sort_order')->label('Orden')->sortable(),
            Tables\Columns\IconColumn::make('is_active')->label('Visible')->boolean(),
        ])->defaultSort('sort_order')->reorderable('sort_order')->actions([
            Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListHeroSlides::route('/'), 'create' => Pages\CreateHeroSlide::route('/create'), 'edit' => Pages\EditHeroSlide::route('/{record}/edit')];
    }

    public static function canViewAny(): bool { return auth()->user()?->can('hero.gestionar') ?? false; }
    public static function canCreate(): bool { return static::canViewAny(); }
    public static function canEdit($record): bool { return static::canViewAny(); }
    public static function canDelete($record): bool { return static::canViewAny(); }
    public static function canReorder(): bool { return static::canViewAny(); }
}
