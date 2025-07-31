<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Pages\CreateModelType;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Pages\EditModelType;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Pages\ListModelType;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Pages\ViewModelType;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Schemas\ModelTypeForm;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Schemas\ModelTypeInfolist;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Tables\ModelTypeTable;
use Valourite\DynamicModels\Models\ModelType;

final class ModelTypeResource extends Resource
{
    protected static ?string $model = ModelType::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Schema $schema): Schema
    {
        return ModelTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ModelTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModelTypeTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListModelType::route('/'),
            'create' => CreateModelType::route('/create'),
            'view'   => ViewModelType::route('/{record}'),
            'edit'   => EditModelType::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return config('dynamic-models.grouped', true) ? config('dynamic-models.group', 'Model Builder') : null;
    }
}
