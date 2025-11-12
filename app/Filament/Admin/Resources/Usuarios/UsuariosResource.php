<?php

namespace App\Filament\Admin\Resources\Usuarios;

use App\Filament\Admin\Resources\Usuarios\Pages\CreateUsuarios;
use App\Filament\Admin\Resources\Usuarios\Pages\EditUsuarios;
use App\Filament\Admin\Resources\Usuarios\Pages\ListUsuarios;
use App\Filament\Admin\Resources\Usuarios\Schemas\UsuariosForm;
use App\Filament\Admin\Resources\Usuarios\Tables\UsuariosTable;
use App\Models\Usuarios;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UsuariosResource extends Resource
{
    protected static ?string $model = Usuarios::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Gestion del Sistema';
    protected static ?string $navigationLabel = 'Usuarios';

    public static function form(Schema $schema): Schema
    {
        return UsuariosForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsuariosTable::configure($table);
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
            'index' => ListUsuarios::route('/'),
            'create' => CreateUsuarios::route('/create'),
            'edit' => EditUsuarios::route('/{record}/edit'),
        ];
    }
}
