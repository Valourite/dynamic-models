<?php

namespace Valourite\DynamicModels\Filament\Support\Helpers;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Valourite\DynamicModels\Concerns\CanIncludeBaseFields;
use Valourite\DynamicModels\Concerns\CanIncludeExtraOptions;
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
    use CanIncludeExtraOptions;
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
            ->slideOver()
            ->modalHeading('Configure Field Options')
            ->schema(function (array $arguments, Get $get) {
                $state    = $get('Fields');
                $itemData = $state[$arguments['item']] ?? [];
                $type = $itemData['type'] ?? null;

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

                return array_values(array_filter($fieldSections));
            })
            ->fillForm(function (array $arguments, Get $get) {
                $state = $get('Fields');

                return $state[$arguments['item']] ?? [];
            })
            ->action(function (array $data, array $arguments, Repeater $component) {
                logger('Inside action');
                $state       = $component->getState();
                $currentItem = $state[$arguments['item']] ?? [];
                $currentType = $currentItem['type'] ?? null;
                $lastType    = $currentItem['last_type'] ?? null;

                // If type has changed, clear type-specific options
                if ($currentType && $lastType && $currentType !== $lastType) {
                    // Define common fields that should be preserved
                    $commonFields = [
                        'name', 'label', 'type', 'custom_id', 'required', 'helper_text', 'last_type',
                    ];

                    // Add feature-specific fields based on the new type
                    $featuresToCheck = [
                        'prefix'      => ['prefix_text', 'suffix_text'],
                        'icon'        => ['prefix_icon', 'suffix_icon', 'prefix_icon_color', 'suffix_icon_color'],
                        'placeholder' => ['placeholder'],
                        'maxlength'   => ['maxlength'],
                        'options'     => ['options'],
                        'searchable'  => ['searchable'],
                        'multiple'    => ['multiple'],
                        'inline'      => ['inline'],
                        'min_value'   => ['min', 'max'],
                        'step'        => ['step'],
                        'rows'        => ['rows', 'cols'],
                        'autosize'    => ['autosize'],
                        'date_format' => ['min_date', 'max_date', 'display_format'],
                        'file_upload' => ['max_file_size', 'accepted_file_types', 'max_files', 'disk', 'directory'],
                    ];

                    // Add fields for supported features
                    foreach ($featuresToCheck as $feature => $fields) {
                        if (FieldRenderer::supportsFeature($currentType, $feature)) {
                            $commonFields = array_merge($commonFields, $fields);
                        }
                    }

                    // Only keep applicable fields for the new type
                    $currentItem = array_intersect_key($currentItem, array_flip($commonFields));
                }

                // Update last_type to current type for future comparisons
                $data['last_type'] = $currentType;

                // Merge data with the filtered current item
                $state[$arguments['item']] = array_merge($currentItem, $data);

                $component->state($state);
            });
    }
}
