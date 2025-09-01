<?php

namespace Valourite\DynamicModels\Concerns;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Valourite\DynamicModels\Filament\Enums\FieldType;

/**
 * This trait will be used to include field type specific options.
 */
trait CanIncludeFieldTypes
{
    /**
     * Get field type options form fields.
     */
    public static function getFieldTypeOptions(string $type): ?array
    {
        $fieldType = FieldType::tryFrom(mb_strtoupper($type)) ?? null;

        if ( ! $fieldType) {
            return null;
        }

        return match($fieldType) {
            FieldType::TEXT => [
                TextInput::make('placeholder')
                    ->label('Placeholder')
                    ->helperText('Text to show when field is empty'),

                TextInput::make('maxlength')
                    ->label('Maximum Length')
                    ->helperText('Maximum number of characters')
                    ->numeric(),
            ],

            FieldType::NUMBER => [
                TextInput::make('min')
                    ->label('Minimum Value')
                    ->helperText('Minimum allowed value')
                    ->numeric(),

                TextInput::make('max')
                    ->label('Maximum Value')
                    ->helperText('Maximum allowed value')
                    ->numeric(),

                TextInput::make('step')
                    ->label('Step')
                    ->helperText('Increment/decrement by this value')
                    ->default('1')
                    ->numeric(),
            ],

            FieldType::EMAIL => [
                TextInput::make('placeholder')
                    ->label('Placeholder')
                    ->helperText('Text to show when field is empty'),

                TextInput::make('maxlength')
                    ->label('Maximum Length')
                    ->helperText('Maximum number of characters')
                    ->numeric(),
            ],

            FieldType::PASSWORD => [
                Toggle::make('revealable')
                    ->label('Revealable')
                    ->helperText('Allow password to be revealed')
                    ->default(true),

                TextInput::make('placeholder')
                    ->label('Placeholder')
                    ->helperText('Text to show when field is empty'),
            ],

            FieldType::TEXTAREA => [
                TextInput::make('placeholder')
                    ->label('Placeholder')
                    ->helperText('Text to show when field is empty'),

                TextInput::make('maxlength')
                    ->label('Maximum Length')
                    ->helperText('Maximum number of characters')
                    ->numeric(),

                TextInput::make('rows')
                    ->label('Rows')
                    ->helperText('Number of visible text rows')
                    ->numeric()
                    ->default(3),

                Toggle::make('autosize')
                    ->label('Auto Size')
                    ->helperText('Automatically adjust height based on content')
                    ->default(false),
            ],

            FieldType::SELECT => [
                Repeater::make('options')
                    ->label('Options')
                    ->schema([
                        TextInput::make('value')
                            ->label('Value')
                            ->required(),
                        TextInput::make('label')
                            ->label('Label')
                            ->required(),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->minItems(1),
            ],

            FieldType::RADIO => [
                Toggle::make('inline')
                    ->label('Inline')
                    ->helperText('Display options horizontally')
                    ->default(false),

                Repeater::make('options')
                    ->label('Options')
                    ->schema([
                        TextInput::make('value')
                            ->label('Value')
                            ->required(),
                        TextInput::make('label')
                            ->label('Label')
                            ->required(),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->minItems(1),
            ],

            FieldType::CHECKBOX => [
                TextInput::make('true_value')
                    ->label('True Value')
                    ->helperText('Value when checked')
                    ->default('1'),

                TextInput::make('false_value')
                    ->label('False Value')
                    ->helperText('Value when unchecked')
                    ->default('0'),
            ],

            FieldType::DATE => [
                TextInput::make('min_date')
                    ->label('Minimum Date')
                    ->helperText('Earliest selectable date (YYYY-MM-DD)'),

                TextInput::make('max_date')
                    ->label('Maximum Date')
                    ->helperText('Latest selectable date (YYYY-MM-DD)'),

                TextInput::make('display_format')
                    ->label('Display Format')
                    ->helperText('Date format (e.g., Y-m-d)')
                    ->default('Y-m-d'),
            ],

            FieldType::TIME => [
                TextInput::make('min_time')
                    ->label('Minimum Time')
                    ->helperText('Earliest selectable time (HH:MM)'),

                TextInput::make('max_time')
                    ->label('Maximum Time')
                    ->helperText('Latest selectable time (HH:MM)'),

                TextInput::make('display_format')
                    ->label('Display Format')
                    ->helperText('Time format (e.g., H:i)')
                    ->default('H:i'),
            ],

            FieldType::DATETIME => [
                TextInput::make('min_date')
                    ->label('Minimum Date/Time')
                    ->helperText('Earliest selectable date/time (YYYY-MM-DD HH:MM)'),

                TextInput::make('max_date')
                    ->label('Maximum Date/Time')
                    ->helperText('Latest selectable date/time (YYYY-MM-DD HH:MM)'),

                TextInput::make('display_format')
                    ->label('Display Format')
                    ->helperText('Date/time format (e.g., Y-m-d H:i)')
                    ->default('Y-m-d H:i'),
            ],

            FieldType::FILE => [
                TextInput::make('disk')
                    ->label('Storage Disk')
                    ->helperText('Storage disk to use')
                    ->default('public'),

                TextInput::make('directory')
                    ->label('Directory')
                    ->helperText('Upload directory path')
                    ->default('uploads'),

                TextInput::make('max_size')
                    ->label('Maximum Size (KB)')
                    ->helperText('Maximum file size in kilobytes')
                    ->numeric()
                    ->default(10240), // 10MB

                TextInput::make('accepted_file_types')
                    ->label('Accepted File Types')
                    ->helperText('Comma separated list, e.g.: image/jpeg,image/png')
                    ->placeholder('image/jpeg,image/png,application/pdf'),

                Toggle::make('multiple')
                    ->label('Multiple Files')
                    ->helperText('Allow multiple file uploads')
                    ->default(false),
            ],

            default => null,
        };
    }

    /**
     * Generate a section with field type specific options.
     */
    public static function getFieldTypeOptionsSection(string $type): ?Component
    {
        $options = static::getFieldTypeOptions($type);

        if (empty($options)) {
            return null;
        }

        $typeLabel = ucfirst(mb_strtolower($type));

        return Section::make("{$typeLabel} Field Options")
            ->schema($options)
            ->columns(2)
            ->collapsible()
            ->collapsed();
    }
}
