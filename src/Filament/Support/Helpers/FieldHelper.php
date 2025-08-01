<?php

namespace Valourite\DynamicModels\Filament\Support\Helpers;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Valourite\DynamicModels\Concerns\CanIncludeBaseFields;
use Valourite\DynamicModels\Concerns\CanIncludeExtraOptions;
use Valourite\DynamicModels\Concerns\CanIncludeIcons;
use Valourite\DynamicModels\Concerns\CanIncludePrefixSuffixText;

/**
 * This class will be used to inject any reused code into the field repeater.
 */
final class FieldHelper
{
    use CanIncludeBaseFields;
    use CanIncludeExtraOptions;
    use CanIncludeIcons;
    use CanIncludePrefixSuffixText;

    public static function getBaseOptionsModal(): Action
    {
        return Action::make('field_options')
            ->icon('heroicon-m-cog')
            ->label('')
            ->tooltip('Edit base field options')
            ->color('gray')
            ->slideOver()
            ->modalHeading('Configure Field Options')
            ->form(function (array $arguments, Get $get) {
                $state    = $get('Fields');
                $itemData = $state[$arguments['item']] ?? [];
                $type     = $itemData['type'] ?? null;

                return array_values(array_filter([
                    //Add is required toggle
                    static::getRequired()->default($itemData['required'] ?? false),

                    //Add helper text
                    static::getHelperText()->default($itemData['helper_text'] ?? ''),

                    //Add prefix and suffix text
                    static::includePrefixSuffixTextOptions(),

                    //Add icon options
                    static::includeIconsOption(),

                    //Add extra options
                    static::getFieldExtraOptions($type),
                ]));
            })
            ->fillForm(function (array $arguments, Get $get) {
                $state = $get('Fields');

                return $state[$arguments['item']] ?? [];
            })
            ->action(function (array $data, array $arguments, Repeater $component) {
                $state                     = $component->getState();
                $state[$arguments['item']] = array_merge($state[$arguments['item']], $data);
                $component->state($state);
            });
    }
}
