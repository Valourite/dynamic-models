<?php

namespace Valourite\DynamicModels\Filament\Support\Components;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Valourite\DynamicModels\Filament\Enums\FieldType;
use Valourite\DynamicModels\Filament\Support\Helpers\FieldHelper;

final class FieldRepeater extends Repeater
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->hiddenLabel()
            ->grid(2)
            ->minItems(1)
            ->addable(true)
            ->deletable(fn($context) => $context === 'create')
            ->reorderable(true)
            ->columnSpanFull()
            ->schema(static::buildSchema())
            ->extraItemActions([
                FieldHelper::getBaseOptionsModal(),
                FieldHelper::getSoftDeleteAction(),
                FieldHelper::getRestoreAction()
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
                    fn (Set $set, ?string $state) => $set('label', str_replace('_', ' ', Str::title(trim($state))))
                )
                ->disabled(fn ($get) => $get('deleted') === true),

            TextInput::make('label')
                ->label('Label')
                ->helperText('This is the label of the field')
                ->disabled(fn ($get) => $get('deleted') === true),

            Select::make('type')
                ->label('Type')
                ->options(
                    collect(FieldType::cases())->mapWithKeys(
                        fn ($type) => [$type->value => Str::title($type->name)]
                    )
                )
                ->default(FieldType::TEXT)
                ->required()
                ->live()
                ->disabled(fn ($get) => $get('deleted') === true),

            FieldHelper::getCustomID('field'),
        ];
    }
}
