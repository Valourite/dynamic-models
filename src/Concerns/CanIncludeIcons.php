<?php

namespace Valourite\DynamicModels\Concerns;

use function Filament\Support\generate_icon_html;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * This trait will be used to include an icon selector into the field or section options.
 */
trait CanIncludeIcons
{
    public static function includeIconsOption()
    {
        return Section::make('Icons')
            ->columns(2)
            ->collapsible()
            ->collapsed()
            ->schema([
                //fetch the prefix icon selector and color picker
                ...static::getIcon('prefix'),

                //fetch the suffix icon selector and color picker
                ...static::getIcon('suffix'),
            ]);
    }

    public static function getIcon($name)
    {
        return [
            static::getIconSelector("{$name}_icon"),

            static::getIconColorPicker("{$name}_icon_color"),
        ];
    }

    public static function getIconColorPicker($name)
    {
        $label = str_replace('-', ' ', $name);

        return ColorPicker::make($name)
            ->label(Str::title($label))
            ->helperText("This is the {$label}.");
    }

    public static function getIconSelector($name): Select
    {
        //TODO: Make icons inline, right now they appear on top of one another
        $options = Cache::remember('dynamic-models.heroicon-options', now()->addHours(6), function () {
            return collect(Heroicon::cases())->mapWithKeys(function (Heroicon $heroicon) {
                $iconName = $heroicon->value;
                $iconHtml = generate_icon_html($heroicon)->toHtml();
                $label    = "<span class='inline-flex items-center'>
                            {$iconHtml}
                            <span class='ml-1'>{$iconName}</span>
                        </span>";

                return [$iconName => $label];
            })->toArray();
        });

        return Select::make($name)
            ->options($options)
            ->searchable()
            ->preload()
            ->allowHtml()
            ->native(false)
            ->helperText('Choose a Heroicon to prefix the field.');
    }
}
