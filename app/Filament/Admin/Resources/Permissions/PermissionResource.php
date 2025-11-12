<?php

namespace App\Filament\Admin\Resources\Permissions;

use App\Filament\Admin\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Admin\Resources\Permissions\Pages\EditPermission;
use App\Filament\Admin\Resources\Permissions\Pages\ListPermissions;
use App\Filament\Admin\Resources\Permissions\Schemas\PermissionForm;
use App\Filament\Admin\Resources\Permissions\Tables\PermissionsTable;
use App\Models\Permission;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    // 🔹 Propiedades de navegación Filament
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static UnitEnum|string|null $navigationGroup = 'Gestion del Sistema';
    protected static ?string $navigationLabel = 'Permisos';


    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
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
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}
