<?php

namespace Valourite\DynamicModels\Filament\Support\Components;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Valourite\DynamicModels\Filament\Enums\FieldType;
use Valourite\DynamicModels\Filament\Support\Helpers\FieldHelper;

final class FieldRepeater extends Repeater
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->label('Model Field')
            ->grid(2)
            ->columnSpanFull()
            ->schema(static::buildSchema())
            ->extraItemActions([
                FieldHelper::getBaseOptionsModal(),
            ]);
    }

    protected static function buildSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(
                    fn(Set $set, ?string $state) =>
                    $set('label', str_replace('_', ' ', Str::title(trim($state))))
                ),

            TextInput::make('label')
                ->label('Label')
                ->helperText('This is the label of the field'),

            Select::make('type')
                ->label('Type')
                ->options(
                    collect(FieldType::cases())->mapWithKeys(
                        fn($type) => [$type->value => Str::title($type->name)]
                    )
                )
                ->default(FieldType::TEXT)
                ->required()
                ->live(),

            FieldHelper::getCustomID('field'),
        ];
    }
}
