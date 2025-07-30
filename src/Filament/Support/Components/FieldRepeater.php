<?php

namespace Valourite\FormBuilder\Filament\Support\Components;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Valourite\FormBuilder\Filament\Enums\FieldType;
use Valourite\FormBuilder\Filament\Support\Helpers\FieldHelper;

final class FieldRepeater extends Repeater
{
    protected static ?array $cachedSchema = null;

    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->label('Form Field')
            ->grid(2)
            ->columnSpanFull()
            ->schema(static::buildSchema());
    }

    protected static function buildSchema(): array
    {
        return [
            Tabs::make()
                ->label('Field')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Field')
                        ->label('Field')
                        ->schema([
                            TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                    $set('label', str_replace('_', ' ', Str::title(trim($state))));
                                }),

                            TextInput::make('label')
                                ->label('Label')
                                ->helperText('This is the label of the field'),

                            Select::make('type')
                                ->label('Type')
                                ->options(
                                    collect(FieldType::cases())
                                        ->mapWithKeys(fn ($type) => [
                                            $type->value => Str::title($type->name),
                                        ])
                                )
                                ->required()
                                ->live(),
                        ]),

                    Tab::make('Options')
                        ->label('Options')
                        ->schema(array_filter([
                            Checkbox::make('required')
                                ->label('Required')
                                ->helperText('Is this field required.'),

                            FieldHelper::select(),

                            FieldHelper::customID('field'),

                            Repeater::make('options')
                                ->label('Options')
                                ->schema([
                                    TextInput::make('label')
                                        ->required()
                                        ->maxLength(255)
                                        ->label('Option Label')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, ?string $state, $context) {
                                            if ($context === 'edit') {
                                                return;
                                            }
                                            $set('value', str_replace(' ', '_', Str::lower(trim($state))));
                                        }),

                                    TextInput::make('value')
                                        ->required()
                                        ->label('Option Value'),
                                ])
                                ->addActionLabel('Add Option')
                                ->minItems(1)
                                ->visible(
                                    fn ($get) => $get('type') === FieldType::SELECT->value ||
                                    $get('type') === FieldType::RADIO->value
                                )
                                ->columnSpanFull(),
                        ])),
                ]),
        ];
    }
}
