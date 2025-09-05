<?php

namespace Valourite\DynamicModels\Filament\Support\Helpers;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Utilities\Get;
use Valourite\DynamicModels\Concerns\CanIncludeBaseFields;
use Valourite\DynamicModels\Concerns\CanIncludeFieldSpecificOptions;
use Valourite\DynamicModels\Concerns\CanIncludeIcons;
use Valourite\DynamicModels\Concerns\CanIncludePrefixSuffixText;
use Valourite\DynamicModels\Filament\Support\Renderers\FieldRenderer;

/**
 * This class will be used to inject any reused code into the field repeater.
 */
final class FieldHelper
{
    use CanIncludeBaseFields;
    use CanIncludeFieldSpecificOptions;
    use CanIncludeIcons;
    use CanIncludePrefixSuffixText;

    public static function getBaseOptionsModal(): Action
    {
        return Action::make('field_options')
            ->icon('heroicon-m-cog')
            ->label('')
            ->tooltip('Edit base field options')
            ->color('gray')
            ->modalHeading('Configure Field Options')
            ->fillForm(function (array $arguments, Get $get) {
                $state = $get('Fields');

                return $state[$arguments['item']] ?? [];
            })
            ->form(function (Get $get, array $arguments, Repeater $component) {
                $item = $component->getItemState($arguments['item']) ?? [];
                $type = $item['type'] ?? null;

                $sections = [];

                // Common
                $sections[] = static::getRequired()->default($item['required'] ?? false);
                $sections[] = static::getHelperText()->default($item['helper_text'] ?? '');

                /*
                 * The fieldRenderer helper functions allows
                 * us to expand for future types
                 */

                // Feature-driven blocks
                if (FieldRenderer::supportsFeature($type, 'placeholder')) {
                    $sections[] = static::getPlaceholderOption()->default($item['placeholder'] ?? '');
                }
                if (FieldRenderer::supportsFeature($type, 'maxlength')) {
                    $sections[] = static::getMaxLengthOption()->default($item['maxlength'] ?? null);
                }
                if (FieldRenderer::supportsFeature($type, 'prefix')) {
                    $sections[] = static::includePrefixSuffixTextOptions();
                }
                if (FieldRenderer::supportsFeature($type, 'icon')) {
                    $sections[] = static::includeIconsOption();
                }
                if (FieldRenderer::supportsFeature($type, 'min_value')) {
                    $sections[] = static::getNumberRangeOptions();
                }
                if (FieldRenderer::supportsFeature($type, 'rows')) {
                    $sections[] = static::getTextAreaOptions();
                }
                if (FieldRenderer::supportsFeature($type, 'inline')) {
                    $sections[] = static::getInlineOption()->default($item['inline'] ?? false);
                }
                if (FieldRenderer::supportsFeature($type, 'options')) {
                    $sections[] = static::getOptionsSection();
                }
                if (FieldRenderer::supportsFeature($type, 'date_format')) {
                    $sections[] = static::getDateOptions();
                }
                if (FieldRenderer::supportsFeature($type, 'file_upload')) {
                    $sections[] = static::getFileUploadOptions();
                }

                return array_values(array_filter($sections));
            })
            ->action(function (array $data, array $arguments, Repeater $component) {
                $state       = $component->getState();
                $currentItem = $state[$arguments['item']] ?? [];

                // Merge data with the filtered current item
                $state[$arguments['item']] = array_merge($currentItem, $data);
                $component->state($state);
            });
    }
}
