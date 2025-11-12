<?php

namespace App\Filament\Admin\Resources\CategoriaMenus\Pages;

use App\Filament\Admin\Resources\CategoriaMenus\CategoriaMenuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaMenu extends EditRecord
{
    protected static string $resource = CategoriaMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
