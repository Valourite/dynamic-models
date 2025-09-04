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
<<<<<<< HEAD
            ->form(function (array $arguments, Get $get) {
                if ( ! isset($arguments['item'])) {
                    return [];
                }

                $state    = $get('Fields');
                $itemData = $state[$arguments['item']] ?? [];
                $type     = $itemData['type'] ?? null;

                // Get common options for all field types
                $commonOptions = [
                    //Add is required toggle
                    static::getRequired()->default($itemData['required'] ?? false),

                    //Add helper text
                    static::getHelperText()->default($itemData['helper_text'] ?? ''),
                ];

                // Determine if this field type supports specific features
                $supportsPrefixSuffix = FieldRenderer::supportsFeature($type, 'prefix');
                $supportsIcons        = FieldRenderer::supportsFeature($type, 'icon');
                $supportsPlaceholder  = FieldRenderer::supportsFeature($type, 'placeholder');
                $supportsMaxlength    = FieldRenderer::supportsFeature($type, 'maxlength');
                $supportsOptions      = FieldRenderer::supportsFeature($type, 'options');
                $supportsDateFormat   = FieldRenderer::supportsFeature($type, 'date_format');
                $supportsFileUpload   = FieldRenderer::supportsFeature($type, 'file_upload');
                $supportsSearchable   = FieldRenderer::supportsFeature($type, 'searchable');
                $supportsMultiple     = FieldRenderer::supportsFeature($type, 'multiple');
                $supportsInline       = FieldRenderer::supportsFeature($type, 'inline');
                $supportsMinMaxValue  = FieldRenderer::supportsFeature($type, 'min_value');
                $supportsStep         = FieldRenderer::supportsFeature($type, 'step');
                $supportsRowsCols     = FieldRenderer::supportsFeature($type, 'rows');
                $supportsAutosize     = FieldRenderer::supportsFeature($type, 'autosize');

                // Build form sections based on field type support
                $fieldSections = [];

                // Add common options
                $fieldSections = array_merge($fieldSections, $commonOptions);

                // Add field sections based on supported features

                // Add placeholder option if supported
                if ($supportsPlaceholder) {
                    $fieldSections[] = static::getPlaceholderOption()
                        ->default($itemData['placeholder'] ?? '');
                }

                // Add maxlength option if supported
                if ($supportsMaxlength) {
                    $fieldSections[] = static::getMaxLengthOption()
                        ->default($itemData['maxlength'] ?? null);
                }

                // Add prefix/suffix section if supported
                if ($supportsPrefixSuffix) {
                    $fieldSections[] = static::includePrefixSuffixTextOptions();
                }

                // Add icons section if supported
                if ($supportsIcons) {
                    $fieldSections[] = static::includeIconsOption();
                }

                // Add number-specific options
                if ($supportsMinMaxValue) {
                    $fieldSections[] = static::getNumberRangeOptions();
                }

                // Add textarea-specific options
                if ($supportsRowsCols) {
                    $fieldSections[] = static::getTextAreaOptions();
                }

                // Add radio/checkbox options for inline display
                if ($supportsInline) {
                    $fieldSections[] = static::getInlineOption()
                        ->default($itemData['inline'] ?? false);
                }

                // Add options section for select/radio/checkbox
                if ($supportsOptions) {
                    $fieldSections[] = static::getOptionsSection();
                }

                // Add date/time options
                if ($supportsDateFormat) {
                    $fieldSections[] = static::getDateOptions();
                }

                // Add file upload options
                if ($supportsFileUpload) {
                    $fieldSections[] = static::getFileUploadOptions();
                }

                // dd($fieldSections);

                return array_values(array_filter($fieldSections));
            })
=======
>>>>>>> tmp
            ->fillForm(function (array $arguments, Get $get) {
                if ( ! isset($arguments['item'])) {
                    return [];
                }

                $state = $get('Fields');
                return $state[$arguments['item']] ?? [];
            })
<<<<<<< HEAD
            ->action(function (array $data, array $arguments, Repeater $component) {
                $state = $component->getState();

                if ( ! isset($arguments['item']) || ! isset($state[$arguments['item']])) {
                    return;
                }

                $currentItem = $state[$arguments['item']] ?? [];
                $currentType = $currentItem['type'] ?? null;
                $lastType    = $currentItem['last_type'] ?? null;
=======
            ->form(function (Get $get, array $arguments, Repeater $component) {
                $item = $component->getItemState($arguments['item']) ?? [];
                $type = $item['type'] ?? null;
>>>>>>> tmp

                $sections = [];

                // Common
                $sections[] = static::getRequired()->default($item['required'] ?? false);
                $sections[] = static::getHelperText()->default($item['helper_text'] ?? '');

                /**
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
                $state = $component->getState();
                $currentItem = $state[$arguments['item']] ?? [];

                // Merge data with the filtered current item
                $state[$arguments['item']] = array_merge($currentItem, $data);
                $component->state($state);
            });
    }
}
