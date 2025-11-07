<?php

namespace App\Filament\Admin\Resources\CategoriaMenus;

use App\Filament\Admin\Resources\CategoriaMenus\Pages\CreateCategoriaMenu;
use App\Filament\Admin\Resources\CategoriaMenus\Pages\EditCategoriaMenu;
use App\Filament\Admin\Resources\CategoriaMenus\Pages\ListCategoriaMenus;
use App\Filament\Admin\Resources\CategoriaMenus\Schemas\CategoriaMenuForm;
use App\Filament\Admin\Resources\CategoriaMenus\Tables\CategoriaMenusTable;
use App\Models\CategoriaMenu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoriaMenuResource extends Resource
{
    protected static ?string $model = CategoriaMenu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CategoriaMenuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriaMenusTable::configure($table);
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
            'index' => ListCategoriaMenus::route('/'),
            'create' => CreateCategoriaMenu::route('/create'),
            'edit' => EditCategoriaMenu::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
