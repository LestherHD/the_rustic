<?php

namespace App\Filament\Admin\Resources\Usuarios\Pages;

use App\Filament\Admin\Resources\Usuarios\UsuariosResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsuarios extends ListRecords
{
    protected static string $resource = UsuariosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
