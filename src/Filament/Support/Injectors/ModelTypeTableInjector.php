<?php

namespace Valourite\DynamicModels\Filament\Support\Injectors;

use Filament\Tables\Columns\TextColumn;

/**
 * The user will use this class to inject the model type table schema into their table schema.
 */
final class ModelTypeTableInjector
{
    public static function make(): array
    {
        return [
            TextColumn::make('modelInstance.modelType.model_type_name')
                ->label('Type')
                ->default('Type Deleted')
                ->sortable()
                ->searchable()
                ->toggleable()
                ->wrap(),
        ];
    }
}
