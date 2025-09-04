<?php

namespace Valourite\DynamicModels\Filament\Support\Helpers;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
            ->fillForm(function (array $arguments, Get $get) {
                $state = $get(ModelType::MODEL_TYPE_SCHEMA);
                return $state[$arguments['item']] ?? [];
            })
            ->form(function (Get $get, array $arguments) {
                $state    = $get(ModelType::MODEL_TYPE_SCHEMA);
                $itemData = $state[$arguments['item']] ?? [];

                return array_values(array_filter([
                    static::getHelperText()->default($itemData['helper_text'] ?? ''),
                    static::getCollapsible()->default($itemData['is_collapsible'] ?? false),
                    static::getColumnSpan()->default($itemData['column_span_full'] ?? false),
                    static::getColumnCount()->default($itemData['column_count'] ?? 1),
                ]));
            })
            ->action(function (array $data, array $arguments, Repeater $component) {
                $state = $component->getState();
                $currentItem = $state[$arguments['item']] ?? [];

                // Merge data with the filtered current item
                $state[$arguments['item']] = array_merge($currentItem, $data);
                $component->state($state);
            });
    }

}
