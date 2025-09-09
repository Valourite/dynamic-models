<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Valourite\DynamicModels\Filament\Support\Generators\ModelTypeSchemaGenerator;
use Valourite\DynamicModels\Models\ModelType;

final class ModelTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(config('dynamic-models.navigation.label', 'Model Type') . ' Details')
                ->schema([
                    TextEntry::make(ModelType::MODEL_TYPE_NAME)
                        ->label(config('dynamic-models.navigation.label', 'Model Type') . ' Name'),

                    TextEntry::make(ModelType::MODEL_TYPE_DESCRIPTION)
                        ->label(config('dynamic-models.navigation.label', 'Model Type') . ' Description')
                        ->markdown(),

                    TextEntry::make(ModelType::MODEL_TYPE_CONFIRMATION_MESSAGE)
                        ->label('Confirmation Message')
                        ->html(),

                    TextEntry::make(ModelType::MODEL_TYPE_VERSION)
                        ->label(config('dynamic-models.navigation.label', 'Model Type') . ' Version'),

                    TextEntry::make(ModelType::MODEL_TYPE_PARENT_MODEL)
                        ->label('Parent Model')
                        ->formatStateUsing(fn ($state) => class_basename($state)),

                    TextEntry::make(ModelType::CAN_BE_CREATED)
                        ->label('Active')
                        ->badge()
                        ->color(fn ($state) => $state ? 'success' : 'warning')
                        ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                ])
                ->columns(2),

            Section::make('Schema Preview')
                ->schema(function (Get $get) {
                    $record = $get('record');

                    // we return the schema and allow the user to play with it -> enter values, they wont be saved
                    return ModelTypeSchemaGenerator::formSchema($record, 'display');
                })
                ->visible(fn (Get $get) => filled($get('record')?->model_type_schema))
                ->columnSpanFull()
                ->columns(1),
        ])->columns(1);
    }
}
