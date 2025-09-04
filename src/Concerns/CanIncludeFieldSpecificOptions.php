<?php

namespace Valourite\DynamicModels\Concerns;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

/**
 * This trait will be used to include field-specific options into the field options form.
 */
trait CanIncludeFieldSpecificOptions
{
    /**
     * Get placeholder option for text fields.
     */
    public static function getPlaceholderOption()
    {
        return TextInput::make('placeholder')
            ->label('Placeholder')
            ->helperText('Text to show when field is empty')
            ->maxLength(255);
    }

    /**
     * Get maximum length option for text fields.
     */
    public static function getMaxLengthOption()
    {
        return TextInput::make('maxlength')
            ->label('Maximum Length')
            ->helperText('Maximum number of characters')
            ->numeric();
    }

    /**
     * Get number range options (min, max, step).
     */
    public static function getNumberRangeOptions()
    {
        return Section::make('Number Options')
            ->columns(3)
            ->collapsible()
            ->collapsed()
            ->schema([
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
            ]);
    }

    /**
     * Get textarea-specific options.
     */
    public static function getTextAreaOptions()
    {
        return Section::make('Textarea Options')
            ->columns(3)
            ->collapsible()
            ->collapsed()
            ->schema([
                TextInput::make('rows')
                    ->label('Rows')
                    ->helperText('Number of visible text rows')
                    ->numeric()
                    ->default(3),

                TextInput::make('cols')
                    ->label('Columns')
                    ->helperText('Number of visible text columns')
                    ->numeric(),

                Toggle::make('autosize')
                    ->label('Auto Size')
                    ->helperText('Automatically adjust height based on content')
                    ->default(false),
            ]);
    }

    /**
     * Get inline display option for radio/checkbox.
     */
    public static function getInlineOption()
    {
        return Toggle::make('inline')
            ->label('Inline Display')
            ->helperText('Display options horizontally')
            ->default(false);
    }

    /**
     * Get options section for select/radio fields.
     */
    public static function getOptionsSection()
    {
        return Section::make('Options')
            ->columns(1)
            ->collapsible()
            ->collapsed()
            ->schema([
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
            ]);
    }

    /**
     * Get date/time field options.
     */
    public static function getDateOptions()
    {
        return Section::make('Date Options')
            ->columns(3)
            ->collapsible()
            ->collapsed()
            ->schema([
                DatePicker::make('min_date')
                    ->label('Minimum Date')
                    ->native(false)
                    ->helperText('Earliest selectable date (YYYY-MM-DD)'),

                DatePicker::make('max_date')
                    ->label('Maximum Date')
                    ->native(false)
                    ->helperText('Latest selectable date (YYYY-MM-DD)'),

                Select::make('display_format')
                    ->label('Display Format')
                    ->helperText('Date format (e.g., Y-m-d)')
                    ->options([
                        'Y-m-d' => 'Y-m-d',
                        'd-m-Y' => 'd-m/Y',
                        'm/d/Y' => 'm/d/Y',
                        'Y/m/d' => 'Y/m/d',
                    ])
                    ->default('Y-m-d'),
            ]);
    }

    /**
     * Get file upload options.
     */
    public static function getFileUploadOptions()
    {
        return Section::make('File Upload Options')
            ->columns(2)
            ->collapsible()
            ->collapsed()
            ->schema([
                TextInput::make('max_file_size')
                    ->label('Maximum Size (MB)')
                    ->helperText('Maximum file size in megabytes')
                    ->numeric()
                    ->default(10), // 10MB

                TextInput::make('accepted_file_types')
                    ->label('Accepted File Types')
                    ->helperText('Comma separated list, e.g.: image/jpeg,image/png')
                    ->placeholder('image/jpeg,image/png,application/pdf'),

                Toggle::make('multiple')
                    ->label('Multiple Files')
                    ->helperText('Allow multiple file uploads')
                    ->default(false),
            ]);
    }
}
