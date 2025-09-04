<?php

namespace Valourite\DynamicModels\Concerns;

use Filament\Schemas\Components\Component;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

/**
 * This trait will be used to apply field options to components.
 */
trait HandlesFieldOptions
{
    /**
     * Apply field options to a component.
     *
     * @param Component $component The component to apply options to
     * @param string    $type      The field type
     * @param array     $options   The options to apply
     *
     * @return Component
     */
    public static function applyFieldOptions(Component $component, string $type, array $options): Component
    {
        // Apply common options first
        if (!empty($options['helper_text'])) {
            $component->helperText($options['helper_text']);
        }

        if (!empty($options['label'])) {
            $component->label($options['label']);
        }

        if (isset($options['required']) && $options['required']) {
            $component->required();
        }

        // Apply prefix/suffix text
        if (
            method_exists($component, 'prefix') && !empty($options['prefix_text']) &&
            self::supportsFeature($type, 'prefix')
        ) {
            $component->prefix($options['prefix_text']);
        }

        if (
            method_exists($component, 'suffix') && !empty($options['suffix_text']) &&
            self::supportsFeature($type, 'suffix')
        ) {
            $component->suffix($options['suffix_text']);
        }

        // Apply prefix/suffix icons if the component supports them
        if (
            method_exists($component, 'prefixIcon') && !empty($options['prefix_icon']) &&
            self::supportsFeature($type, 'icon')
        ) {
            $icon = $options['prefix_icon'];
            if (is_string($icon) && !str_contains($icon, '\\')) {
                $icon = Heroicon::from($icon);
            }
            $component->prefixIcon($icon);

            if (!empty($options['prefix_icon_color'])) {
                $component->prefixIconColor($options['prefix_icon_color']);
            }
        }

        if (
            method_exists($component, 'suffixIcon') && !empty($options['suffix_icon']) &&
            self::supportsFeature($type, 'icon')
        ) {
            $icon = $options['suffix_icon'];
            if (is_string($icon) && !str_contains($icon, '\\')) {
                $icon = Heroicon::from($icon);
            }
            $component->suffixIcon($icon);

            if (!empty($options['suffix_icon_color'])) {
                $component->suffixIconColor($options['suffix_icon_color']);
            }
        }

        // Apply type-specific options
        $type = mb_strtolower($type);

        // Number field options
        if ($type === 'number') {
            if (isset($options['min']) && method_exists($component, 'minValue')) {
                $component->minValue((float) $options['min']);
            }
            if (isset($options['max']) && method_exists($component, 'maxValue')) {
                $component->maxValue((float) $options['max']);
            }
            if (isset($options['step']) && method_exists($component, 'step')) {
                $component->step((float) $options['step']);
            }
        }

        // Placeholder support
        if (self::supportsFeature($type, 'placeholder') && isset($options['placeholder'])) {
            $component->placeholder($options['placeholder']);
        }

        // Text length limits
        if (self::supportsFeature($type, 'maxlength') && isset($options['maxlength'])) {
            $component->maxLength((int) $options['maxlength']);
        }

        // Textarea specific options
        if ($type === 'textarea') {
            if (isset($options['rows']) && method_exists($component, 'rows')) {
                $component->rows((int) $options['rows']);
            }
            if (isset($options['cols']) && method_exists($component, 'cols')) {
                $component->cols((int) $options['cols']);
            }
            if (isset($options['autosize']) && $options['autosize'] && method_exists($component, 'autosize')) {
                $component->autosize();
            }
        }

        // Options for select and radio fields
        if (self::supportsFeature($type, 'options') && !empty($options['options']) && is_array($options['options'])) {
            // Format options array if necessary
            if (isset($options['options'][0]) && is_array($options['options'][0])) {
                $formattedOptions = collect($options['options'])->mapWithKeys(function ($option) {
                    $value = $option['value'] ?? null;
                    $label = $option['label'] ?? null;

                    if ($value !== null && $label !== null) {
                        return [$value => $label];
                    }

                    return [];
                })->toArray();

                $component->options($formattedOptions);
            } else {
                $component->options($options['options']);
            }
        } elseif (self::supportsFeature($type, 'options')) {
            // Always provide at least an empty array of options to prevent null errors
            $component->options([]);
        }

        // Radio specific options
        if ($type === 'radio' && isset($options['inline']) && $options['inline'] && method_exists($component, 'inline')) {
            $component->inline();
        }

        // Checkbox specific options
        if ($type === 'checkbox') {
            if (isset($options['inline']) && $options['inline'] && method_exists($component, 'inline')) {
                $component->inline();
            }
            if (isset($options['true_value']) && method_exists($component, 'trueValue')) {
                $component->trueValue($options['true_value']);
            }
            if (isset($options['false_value']) && method_exists($component, 'falseValue')) {
                $component->falseValue($options['false_value']);
            }
        }

        // Date/time field options
        if (self::supportsFeature($type, 'date_format')) {
            if (isset($options['min_date']) && method_exists($component, 'minDate')) {
                $component->minDate($options['min_date']);
            }
            if (isset($options['max_date']) && method_exists($component, 'maxDate')) {
                $component->maxDate($options['max_date']);
            }
            if (isset($options['display_format']) && method_exists($component, 'displayFormat')) {
                $component->displayFormat($options['display_format']);
            }
        }

        // Time-specific options
        if ($type === 'time') {
            if (isset($options['min_time']) && method_exists($component, 'minTime')) {
                $component->minTime($options['min_time']);
            }
            if (isset($options['max_time']) && method_exists($component, 'maxTime')) {
                $component->maxTime($options['max_time']);
            }
        }

        // File upload specific options
        if ($type === 'file') {
            if (isset($options['max_file_size']) && method_exists($component, 'maxSize')) {
                // Convert MB to KB (which is what Filament expects)
                $sizeInKB = (int) $options['max_file_size'] * 1024;
                $component->maxSize($sizeInKB);
            }
            if (isset($options['accepted_file_types']) && method_exists($component, 'acceptedFileTypes')) {
                $component->acceptedFileTypes(explode(',', $options['accepted_file_types']));
            }
            if (isset($options['max_files']) && method_exists($component, 'maxFiles')) {
                $component->maxFiles($options['max_files']);
            }
            if (isset($options['multiple']) && $options['multiple'] && method_exists($component, 'multiple')) {
                $component->multiple();
            }
        }

        // Apply any remaining options using method name conversion
        $ignoredOptions = [
            'helper_text',
            'label',
            'required',
            'placeholder',
            'maxlength',
            'prefix_text',
            'suffix_text',
            'prefix_icon',
            'suffix_icon',
            'prefix_icon_color',
            'suffix_icon_color',
            'min',
            'max',
            'step',
            'options',
            'searchable',
            'multiple',
            'inline',
            'min_date',
            'max_date',
            'display_format',
            'rows',
            'cols',
            'autosize',
            'type',
            'custom_id',
            'max_file_size',
            'accepted_file_types',
            'max_files',
            'min_time',
            'max_time',
            'true_value',
            'false_value',
            'last_type',
        ];

        foreach ($options as $key => $value) {
            if (in_array($key, $ignoredOptions)) {
                continue;
            }

            $method = Str::camel($key);
            if (method_exists($component, $method)) {
                $component->{$method}($value);
            }
        }

        return $component;
    }
}
