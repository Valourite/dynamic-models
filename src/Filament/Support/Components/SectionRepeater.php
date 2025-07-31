<?php

namespace Valourite\DynamicModels\Filament\Support\Components;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Valourite\DynamicModels\Filament\Support\Helpers\FieldHelper;

final class SectionRepeater extends Repeater
{
    public static function make(?string $name = null): static
    {
        $component = parent::make($name);

        $component
            ->label('Section')
            ->collapsible()
            ->collapsed()
            ->minItems(1)
            ->schema([
                Tabs::make()
                    ->label('Section')
                    ->tabs([
                        Tab::make('Section')
                            ->label('Section')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Title')
                                    ->required(),

                                FieldRepeater::make('Fields'),
                            ]),

                        Tab::make('Options')
                            ->label('Options')
                            ->schema([
                                FieldHelper::select(),

                                FieldHelper::customID('sec'),
                            ]),
                    ]),
            ]);

        return $component;
    }
}
