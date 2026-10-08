<?php

namespace App\Filament\Resources\ProductCommentResource\Pages;

use App\Filament\Resources\ProductCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductComment extends CreateRecord
{
    protected static string $resource = ProductCommentResource::class;
}
