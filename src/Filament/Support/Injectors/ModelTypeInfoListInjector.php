<?php

namespace Valourite\DynamicModels\Filament\Support\Injectors;

use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Valourite\DynamicModels\Filament\Support\Generators\ModelTypeSchemaGenerator;

/**
 * The user will use this class to inject the model type infolist schema into their infolist schema.
 */
final class ModelTypeInfoListInjector
{
    public static function make(): array
    {
        return [
            Group::make()
                ->schema(function (Get $get) {
                    $record = $get('record');

                    $modelInstance = $record?->modelInstance ?? null;

                    if ( ! $record || null === $modelInstance) {
                        return [];
                    }

                    return ModelTypeSchemaGenerator::infolistSchema($modelInstance);
                })
                ->columnSpanFull(),
        ];
    }
}
