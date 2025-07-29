<?php

namespace Valourite\FormBuilder\Filament\Support\Injectors;

use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Valourite\FormBuilder\Filament\Support\Generators\FormGenerator;

/**
 * The user will use this class to inject the form infolist schema into their infolist schema
 */
class FormInfoListInjector
{
    public static function make(): array
    {
        return [
            Group::make()
                ->schema(function (Get $get) {

                    $record = $get('record');

                    $response = $record?->response ?? null;

                    if (!$record || is_null($response)) { 
                        return [];
                    }

                    return FormGenerator::infolistSchema($response);
                })
                ->columnSpanFull()
        ];
    }
}