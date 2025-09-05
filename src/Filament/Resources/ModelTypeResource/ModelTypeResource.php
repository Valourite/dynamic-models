<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
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

    protected static string | BackedEnum | null $navigationIcon = Heroicon::DocumentText;

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

    public static function getLabel(): ?string
    {
        return config('dynamic-models.navigation.label') ? __(config('dynamic-models.navigation.label')) : __('Dynamic Model');
    }

    public static function getPluralModelLabel(): string
    {
        return config('dynamic-models.navigation.plural_label') ? __(config('dynamic-models.navigation.plural_label')) : __('Dynamic Models');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return config('dynamic-models.navigation.grouped', true) ? __(config('dynamic-models.navigation.group', 'Model Builder')) : null;
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return config('dynamic-models.navigation.icon') ? config('dynamic-models.navigation.icon') : self::$navigationIcon;
    }

    public static function getNavigationSort(): ?int
    {
        return config('dynamic-models.navigation.sort', true);
    }
}
