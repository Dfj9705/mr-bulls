<?php

namespace App\Filament\Resources\ProductCommentResource\Pages;

use App\Filament\Resources\ProductCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductComment extends EditRecord
{
    protected static string $resource = ProductCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
