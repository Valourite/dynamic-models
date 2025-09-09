<?php

namespace Valourite\DynamicModels\Filament\Support\Injectors;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Illuminate\Database\Eloquent\Model;
use Valourite\DynamicModels\Filament\Support\Generators\ModelTypeSchemaGenerator;
use Valourite\DynamicModels\Models\ModelType;

/**
 * The user will use this class to inject the model type form schema into their form schema.
 */
final class ModelTypeSchemaInjector
{
    public static function make(): array
    {
        return [
            //The select that allows a user to select a model
            //Only visible on create, not edit, as we don't want the user to change the model type after creation
            //This means that models created without a type will never be able to get a type after creation
            Select::make(ModelType::MODEL_TYPE_ID)
                ->label('Type')
                ->visible(fn ($context) => $context === 'create')
                ->live()
                ->options(function ($model) {
                    //We do not display is trashed in here as
                    // users should not be able to create a model of a type that is trashed
                    // This only displays on create, not edit
                    return ModelType::query()
                        ->where(ModelType::MODEL_TYPE_PARENT_MODEL, $model)
                        ->where(ModelType::CAN_BE_CREATED, true)
                        ->get()
                        ->mapWithKeys(fn ($model) => [
                            $model->{ModelType::MODEL_TYPE_ID} => $model->{ModelType::MODEL_TYPE_NAME} . ' - v' . $model->{ModelType::MODEL_TYPE_VERSION},
                        ])
                        ->toArray();
                })
                ->native(false)
                ->afterStateHydrated(function (?Model $record, Component $component) {
                    $component->state($record?->modelInstance?->model_type_id);
                })
                ->required(),

            //The gorup that generates the model type schema based on the selected model type
            Group::make()
                ->schema(function (callable $get, $context) {
                    $modelTypeID = $get(ModelType::MODEL_TYPE_ID);
                    if ( ! filled($modelTypeID)) {
                        return []; // return empty schema if no modelType selected
                    }

                    return ModelTypeSchemaGenerator::formSchema($modelTypeID, $context);
                })
                ->visible(fn (callable $get) => filled($get(ModelType::MODEL_TYPE_ID)))
                ->columnSpanFull(),
        ];
    }
}
