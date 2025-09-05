<?php

namespace Valourite\DynamicModels\Concerns;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

/**
 * This trait will be used to include prefix and suffix text into the field or section options.
 */
trait CanIncludePrefixSuffixText
{
    public static function includePrefixSuffixTextOptions()
    {
        return Section::make('Prefix / Suffix Text')
            ->columns(2)
            ->collapsible()
            ->collapsed()
            ->schema([
                static::getText('prefix_text'),
                static::getText('suffix_text'),
            ]);
    }

    public static function getText($name): TextInput
    {
        $label = str_replace('_', ' ', $name);

        return TextInput::make($name)
            ->label(Str::title($label))
            ->helperText("This is the {$label}.")
            ->maxLength(255);
    }
}
