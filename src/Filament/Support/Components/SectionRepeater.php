<?php

namespace Valourite\DynamicModels\Filament\Support\Components;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Valourite\DynamicModels\Filament\Support\Helpers\SectionHelper;

final class SectionRepeater extends Repeater
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->label('Model Section')
            ->collapsible()
            // ->collapsed()
            ->minItems(1)
            ->columnSpanFull()
            ->schema(static::buildSchema())
            ->extraItemActions([
                SectionHelper::getBaseOptionsModal(),
            ]);
    }

    protected static function buildSchema(): array
    {
        return [
            TextInput::make('title')
                ->label('Title')
                ->required(),

            FieldRepeater::make('Fields'),

            SectionHelper::getCustomID('section'),

            //Add hidden fields
            //TODO: This is nasty, we need to implement it better
            SectionHelper::getHiddenField('helper_text'),
            SectionHelper::getHiddenField('column_span_full'),
            SectionHelper::getHiddenField('column_count'),
            SectionHelper::getHiddenField('is_collapsible'),
        ];
    }
}
