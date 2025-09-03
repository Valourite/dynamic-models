<?php

namespace Valourite\DynamicModels\Concerns;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Valourite\DynamicModels\Filament\Enums\FieldType;

/**
 * This trait will be used to include extra options into the field or section options.
 */
trait CanIncludeExtraOptions
{
    use CanIncludeFieldTypes;

    public static function getFieldExtraOptions(null|FieldType|string $field = null): ?Component
    {
        if ( ! $field) {
            return null;
        }

        $field = is_string($field) ? FieldType::tryFrom($field) : $field;

        return match ($field) {
            FieldType::TEXT => Section::make('Text Settings')
                ->columns(2)
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('max_length')
                        ->label('Max Length')
                        ->numeric()
                        //TODO: Default is not being set
                        ->default(255)
                        ->helperText('Maximum number of characters.'),

                    //TODO: These text masks need to conform to Filament masks
                    Select::make('text_mask')
                        ->label('Text Mask')
                        ->options([
                            'uppercase'  => 'Uppercase (UPPERCASE)',
                            'lowercase'  => 'Lowercase (lowercase)',
                            'snake_case' => 'Snake Case (snake_case)',
                            'camelCase'  => 'Camel Case (camelCase)',
                        ])
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->helperText('Choose a predefined text mask.'),
                ]),

            FieldType::NUMBER => Section::make('Number Settings')
                ->columns(2)
                ->collapsible()
                ->collapsed()
                ->schema([
                    Toggle::make('float')
                        ->label('Allow Float')
                        ->helperText('Enable floating point numbers.'),

                    TextInput::make('precision')
                        ->label('Precision')
                        ->numeric()
                        ->helperText('Decimal precision.'),

                    TextInput::make('size')
                        ->label('Size')
                        ->numeric(),

                    TextInput::make('prefix')
                        ->label('Prefix Text'),

                    TextInput::make('maxValue')
                        ->label('Maximum Value')
                        ->numeric(),

                    TextInput::make('minValue')
                        ->label('Minimum Value')
                        ->numeric(),
                ]),

            FieldType::SELECT, FieldType::RADIO => Section::make('Selectable Options')
                ->columns(1)
                ->collapsible()
                ->collapsed()
                ->schema([
                    Toggle::make('allow_multiple')
                        ->label('Allow Multiple Selections'),

                    Repeater::make('options')
                        ->label('Options')
                        ->schema([
                            TextInput::make('label')
                                ->required()
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
                        ->columnSpanFull(),
                ]),

            FieldType::DATE => Section::make('Date Settings')
                ->columns(1)
                ->collapsible()
                ->collapsed()
                ->schema([
                    Select::make('format')
                        ->label('Date Format')
                        ->options([
                            'Y-m-d' => 'YYYY-MM-DD',
                            'd/m/Y' => 'DD/MM/YYYY',
                            'm-d-Y' => 'MM-DD-YYYY',
                        ])
                        ->searchable()
                        ->preload()
                        ->native(false),

                    //TODO: Implement rules for start_date_range and end_date_range
                    //and show validation message on error
                    DatePicker::make('start_date_range')
                        ->label('Start Date Range')
                        // ->live()
                        ->helperText('Start date range.')
                        ->native(false),

                    DatePicker::make('end_date_range')
                        ->label('End Date Range')
                        // ->reactive()
                        // ->rules(['nullable', 'after_or_equal:start_date_range'])
                        // ->minDate(fn($get) => $get('start_date_range'))
                        ->helperText('End date range.')
                        ->native(false),
                ]),

            FieldType::TIME => Section::make('Time Settings')
                ->columns(1)
                ->collapsible()
                ->collapsed()
                ->schema([
                    Select::make('format')
                        ->label('Time Format')
                        ->options([
                            'H:i'   => '24-hour (HH:MM)',
                            'h:i A' => '12-hour (HH:MM AM/PM)',
                        ])
                        ->searchable()
                        ->preload()
                        ->native(false),

                    //TODO: Implement rules for start_time_range and end_time_range
                    //and show validation message on error
                    TimePicker::make('start_time_range')
                        ->label('Start Time Range')
                        // ->live()
                        ->helperText('Start time range.')
                        ->native(false),

                    TimePicker::make('end_time_range')
                        ->label('End Time Range')
                        // ->reactive()
                        // ->minDate(fn ($get) => $get('start_time_range'))
                        ->helperText('End time cannot be before start.')
                        ->native(false),
                ]),

            FieldType::DATETIME => Section::make('DateTime Settings')
                ->columns(1)
                ->collapsible()
                ->collapsed()
                ->schema([
                    Select::make('format')
                        ->label('DateTime Format')
                        ->options([
                            'Y-m-d H:i'   => 'YYYY-MM-DD HH:MM',
                            'd/m/Y h:i A' => 'DD/MM/YYYY HH:MM AM/PM',
                        ])
                        ->searchable()
                        ->preload()
                        ->native(false),

                    //TODO: Implement rules for start_date_time_range and end_date_time_range
                    //and show validation message on error
                    DateTimePicker::make('start_date_time_range')
                        ->label('Start DateTime Range')
                        // ->live()
                        ->helperText('Start date time range.')
                        ->native(false),

                    DateTimePicker::make('end_date_time_range')
                        ->label('End DateTime Range')
                        // ->reactive()
                        // ->minDate(fn ($get) => $get('start_date_time_range'))
                        ->helperText('End datetime cannot be before start.')
                        ->native(false),
                ]),

            default => null,
        };
    }
}
