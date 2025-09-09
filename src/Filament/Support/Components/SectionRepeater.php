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
            ->hiddenLabel()
            ->collapsible()
            ->collapsed(false)
            ->minItems(1)
            ->addable(true)
            ->deletable(fn ($context) => $context === 'create')
            ->reorderable(true)
            ->columnSpanFull()
            ->schema(static::buildSchema())
            ->extraItemActions([
                SectionHelper::getBaseOptionsModal(),
                SectionHelper::getSoftDeleteAction(),
                SectionHelper::getRestoreAction(),
            ]);
    }

    protected static function buildSchema(): array
    {
        return [
            TextInput::make('title')
                ->label('Title')
                ->required()
                ->disabled(fn ($get) => $get('deleted') === true),

            FieldRepeater::make('Fields')
                ->disabled(fn ($get) => $get('deleted') === true),

            SectionHelper::getCustomID('section'),
        ];
    }
}
