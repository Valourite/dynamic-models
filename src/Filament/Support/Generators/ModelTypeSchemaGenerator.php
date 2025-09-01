<?php

namespace Valourite\DynamicModels\Filament\Support\Generators;

use DateTime;
use Exception;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Throwable;
use Valourite\DynamicModels\Filament\Support\Renderers\FieldRenderer;
use Valourite\DynamicModels\Models\ModelInstance;
use Valourite\DynamicModels\Models\ModelInstanceValue;
use Valourite\DynamicModels\Models\ModelType;

final class ModelTypeSchemaGenerator
{
    private static array $componentCache = [];

    /**
     * Generates the form schema that can be appended to the models form.
     *
     * @param int|\Valourite\DynamicModels\Models\ModelType $modelType
     *
     * @return array
     */
    public static function formSchema(int|ModelType $modelType): array
    {
        $modelType = $modelType instanceof ModelType ? $modelType : ModelType::findOrFail($modelType);
        $modelTypeSchema = $modelType->model_type_schema ?? [];

        // Log the entire schema for debugging
        $components = [];

        foreach ($modelTypeSchema as $sectionIndex => $section) {
            $fields = [];
            foreach ($section['Fields'] ?? [] as $fieldIndex => $field) {
                $fieldID = $field['custom_id'] ?? null;
                $type = $field['type'] ?? 'text';

                if (!$fieldID) {
                    continue;
                }

                // Create the component with all field options
                $component = FieldRenderer::render($type, $fieldID, $field);

                // Set default state for required select fields to prevent validation errors
                if (
                    $type === 'select' && isset($field['required']) && $field['required']
                    && $component instanceof \Filament\Forms\Components\Select
                ) {
                    // Get the first option as default value if options exist
                    if (!empty($field['options']) && is_array($field['options']) && count($field['options']) > 0) {
                        $firstOption = $field['options'][0] ?? null;
                        if ($firstOption && isset($firstOption['value'])) {
                            $defaultValue = $firstOption['value'];

                            // If multiple select, make it an array
                            if (isset($field['multiple']) && $field['multiple']) {
                                $defaultValue = [$defaultValue];
                            }

                            $component->default($defaultValue);

                        }
                    }

                    // Handle afterStateHydrated for retrieving values
                    $component->afterStateHydrated(
                        fn(Component $component, $state) => static::hydrateResponseState($component, $fieldID)
                    );

                    $fields[] = $component;
                }

                if (!empty($fields)) {
                    $sectionTitle = $section['title'] ?? 'Section';
                    $sectionComponent = Section::make($sectionTitle)
                        ->schema($fields);

                    if (!empty($section['helper_text'])) {
                        $sectionComponent->description($section['helper_text']);
                    }

                    if (!empty($section['column_count'])) {
                        $sectionComponent->columns((int) $section['column_count']);
                    }

                    if (!empty($section['is_collapsible'])) {
                        $sectionComponent->collapsible();
                    }

                    if (!empty($section['column_span_full'])) {
                        $sectionComponent->columnSpanFull();
                    }

                    $components[] = $sectionComponent;
                }
            }

            return $components;

        }
    }

    //TODO: See about a helper function that can always grab the value based on a fieldID

    /**
     * Generates the infolist schema that can be appended to the models infolist.
     *
     * @param int|\Valourite\DynamicModels\Models\ModelInstance $modelInstance
     *
     * @return array
     */
    public static function infolistSchema(int|ModelInstance $modelInstance): array
    {
        $modelInstance = $modelInstance instanceof ModelInstance
            ? $modelInstance
            : ModelInstance::findOrFail($modelInstance);

        $modelTypeSchema = $modelInstance->modelType?->model_type_schema ?? [];
        $instanceData = $modelInstance?->modelInstanceValues->pluck(ModelInstanceValue::VALUE, ModelInstanceValue::FIELD_ID);

        $entries = [];

        foreach ($modelTypeSchema as $section) {
            $sectionTitle = $section['title'] ?? 'Section';
            $fields = [];

            foreach ($section['Fields'] ?? [] as $field) {
                $fieldId = $field['custom_id'] ?? null;
                if (!$fieldId) {
                    continue;
                }

                $label = $field['label'] ?? $field['name'] ?? 'Field';
                $value = $instanceData[$fieldId] ?? null;
                $type = $field['type'] ?? 'text';

                // Format value based on field type
                $value = match ($type) {
                    'boolean', 'checkbox' => $value ? 'Yes' : 'No',
                    'date' => static::formatDate($value),
                    'datetime' => static::formatDateTime($value),
                    'time' => static::formatTime($value),
                    'select' => static::formatSelectValue($value),
                    default => $value,
                };

                // Create a text entry with the formatted value
                $entry = TextEntry::make($fieldId)
                    ->label($label);

                // Apply different formatters based on field type
                if ($type === 'date') {
                    $entry->date();
                } elseif ($type === 'datetime') {
                    $entry->dateTime();
                } elseif ($type === 'time') {
                    $entry->time();
                } elseif ($type === 'boolean' || $type === 'checkbox') {
                    $entry->badge();
                } elseif ($type === 'select' && str_starts_with($value, '[') && str_ends_with($value, ']')) {
                    // Format select values as bulleted list if it's a JSON array
                    try {
                        $decoded = json_decode($value, true);
                        if (is_array($decoded) && count($decoded) > 1) {
                            $entry->listWithLineBreaks();
                        }
                    } catch (Throwable) {
                        // Ignore errors and fall back to default
                    }
                }

                $fields[] = $entry->state($value ?? '-');
            }

            if (!empty($fields)) {
                $sectionComponent = Section::make($sectionTitle)
                    ->schema($fields);

                // Column count
                if (!empty($section['column_count'])) {
                    $sectionComponent->columns((int) $section['column_count']);
                }

                // Full width
                if (!empty($section['column_span_full'])) {
                    $sectionComponent->columnSpanFull();
                }

                // Collapsible
                if (!empty($section['is_collapsible'])) {
                    $sectionComponent->collapsible();
                }

                // Description (helper text)
                if (!empty($section['helper_text'])) {
                    $sectionComponent->description($section['helper_text']);
                }

                $entries[] = $sectionComponent;
            }
        }

        return $entries;
    }

    private static function formatDate($value): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            if ($value instanceof \Carbon\Carbon || $value instanceof DateTime) {
                return $value->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private static function formatDateTime($value): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            if ($value instanceof \Carbon\Carbon || $value instanceof DateTime) {
                return $value->format('Y-m-d H:i:s');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private static function formatTime($value): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            if ($value instanceof \Carbon\Carbon || $value instanceof DateTime) {
                return $value->format('H:i:s');
            }

            return \Carbon\Carbon::parse($value)->format('H:i:s');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private static function formatSelectValue($value): string
    {
        if (empty($value)) {
            return '-';
        }

        // Handle JSON-encoded arrays
        if (is_string($value) && str_starts_with($value, '[') && str_ends_with($value, ']')) {
            try {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    return implode(', ', $decoded);
                }
            } catch (Throwable) {
                // Fall through to default handling
            }
        }

        return (string) $value;
    }

    private static function hasMethod(Component $component, string $method): bool
    {
        return method_exists($component, $method);
    }

    private static function getRenderedFieldComponent(string $type, string $fieldID): Component
    {
        //This might not work
        $cacheKey = "{$type}:{$fieldID}";

        return static::$componentCache[$cacheKey] ??= FieldRenderer::render($type, $fieldID);
    }

    private static function hydrateResponseState(Component $component, string $fieldID): void
    {
        $record = $component->getLivewire()?->record;
        $instance = $record?->modelInstance;

        // If no record or instance, just return
        if (!$instance) {
            return;
        }

        $values = $instance->modelInstanceValues->pluck(ModelInstanceValue::VALUE, ModelInstanceValue::FIELD_ID);
        $types = $instance->modelInstanceValues->pluck(ModelInstanceValue::TYPE, ModelInstanceValue::FIELD_ID);

        $value = $values[$fieldID] ?? null;
        $type = $types[$fieldID] ?? null;

        // Skip if there's no value to hydrate
        if ($value === null) {
            // For date/time fields, provide a default value to prevent validation errors
            if ($component instanceof \Filament\Forms\Components\DatePicker) {
                $value = now()->startOfDay();
                $component->state($value);
                return;
            }

            if ($component instanceof \Filament\Forms\Components\DateTimePicker) {
                $value = now();
                $component->state($value);

                return;
            }
            if ($component instanceof \Filament\Forms\Components\TimePicker) {
                $value = now();
                $component->state($value);

                return;
            }
            if ($component instanceof \Filament\Forms\Components\Select && $component->isMultiple()) {
                $value = [];
                $component->state($value);

                return;
            }

            return;
        }

        // Handle different field types based on component type and field value

        // Handle Select fields (including multiple select)
        if ($component instanceof \Filament\Forms\Components\Select) {
            // Handle multiple select with JSON values
            if (
                $component->isMultiple() && is_string($value) &&
                str_starts_with($value, '[') && str_ends_with($value, ']')
            ) {
                try {
                    $decodedValue = json_decode($value, true);
                    if (is_array($decodedValue)) {
                        $value = $decodedValue;
                    } else {
                        // Invalid JSON, use empty array
                        $value = [];
                    }
                } catch (Exception $e) {
                    $value = [];
                }
            }

            // Always ensure multiple select has an array value
            if ($component->isMultiple() && !is_array($value)) {
                if (empty($value)) {
                    $value = [];
                } else {
                    $value = [$value];
                }
            }
        }

        // Handle Date/DateTime/Time fields
        elseif (is_string($value)) {
            if ($component instanceof \Filament\Forms\Components\DatePicker) {
                try {
                    // Parse as date only (Y-m-d)
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        $date = \Carbon\Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
                    } else {
                        $date = \Carbon\Carbon::parse($value)->startOfDay();
                    }
                    $value = $date;
                } catch (Exception $e) {
                    // Use current date as fallback
                    $value = now()->startOfDay();
                }
            } elseif ($component instanceof \Filament\Forms\Components\DateTimePicker) {
                try {
                    // Try specific format first
                    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                        $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value);
                    } else {
                        // Fall back to flexible parsing
                        $date = \Carbon\Carbon::parse($value);
                    }
                    $value = $date;
                } catch (Exception $e) {
                    // Use current datetime as fallbac
                    $value = now();
                }
            } elseif ($component instanceof \Filament\Forms\Components\TimePicker) {
                try {
                    // Parse time format
                    if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                        $format = mb_strlen($value) === 8 ? 'H:i:s' : 'H:i';
                        $time = \Carbon\Carbon::createFromFormat($format, $value);
                    } else {
                        $time = \Carbon\Carbon::parse($value);
                    }
                    $value = $time;
                } catch (Exception $e) {
                    // Use current time as fallbac
                    $value = now();
                }
            }
        }

        // Set the component state with the processed value
        $component->state($value);
    }
}
