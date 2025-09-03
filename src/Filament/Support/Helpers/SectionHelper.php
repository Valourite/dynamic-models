<?php

namespace Valourite\DynamicModels\Filament\Support\Helpers;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Utilities\Get;
use Valourite\DynamicModels\Concerns\CanIncludeBaseFields;
use Valourite\DynamicModels\Concerns\CanIncludeHiddenFields;
use Valourite\DynamicModels\Concerns\CanIncludeIcons;
use Valourite\DynamicModels\Concerns\CanIncludeSectionOptions;
use Valourite\DynamicModels\Models\ModelType;

/**
 * This class will be used to inject any reused code into the section repeater.
 */
final class SectionHelper
{
    use CanIncludeBaseFields;
    use CanIncludeHiddenFields;
    use CanIncludeIcons;
    use CanIncludeSectionOptions;

    public static function getBaseOptionsModal(): Action
    {
        return Action::make('section_options')
            ->icon('heroicon-m-cog')
            ->label('')
            ->tooltip('Edit base section options')
            ->color('gray')
            ->slideOver()
            ->modalHeading('Configure Section Options')
            ->form(function (array $arguments, Get $get) {
                if ( ! isset($arguments['item'])) {
                    return [];
                }

                $state    = $get(ModelType::MODEL_TYPE_SCHEMA);
                $itemData = $state[$arguments['item']] ?? [];

                // dd($state);

                return array_values(array_filter([
                    //Add helper text
                    static::getHelperText()->default($itemData['helper_text'] ?? ''),

                    //Add collapsible
                    static::getCollapsible(),

                    //Add column span
                    static::getColumnSpan(),

                    //Add column count option
                    static::getColumnCount(),
                ]));
            })
            ->fillForm(function (array $arguments, Get $get) {
                if ( ! isset($arguments['item'])) {
                    return [];
                }

                $state = $get(ModelType::MODEL_TYPE_SCHEMA);

                return $state[$arguments['item']] ?? [];
            })
            ->action(function (array $data, array $arguments, Repeater $component) {
                $state = $component->getState();

                if ( ! isset($arguments['item']) || ! isset($state[$arguments['item']])) {
                    return;
                }

                $state[$arguments['item']] = array_merge($state[$arguments['item']], $data);
                $component->state($state);
            });
    }
}
