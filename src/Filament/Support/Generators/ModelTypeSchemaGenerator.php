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

        //stores the components to be returned
        $components = [];

        //stored the individual fields
        $fields = [];

        foreach ($modelTypeSchema as $section) {
            foreach ($section['Fields'] ?? [] as $field) {
                $fieldID = $field['custom_id'] ?? null;
                $type = $field['type'] ?? 'text';

                if (!$fieldID) {
                    continue;
                }

                // Create the component with all field options
                $component = FieldRenderer::render($type, $fieldID, $field);

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

                //empty fields for next iteration
                $fields = [];

                $components[] = $sectionComponent;
            }
        }

        //TODO: maybe we can cache this schema for future use
        //If it gets changed, we can clear the cache and recache it

        return $components;
    }

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

        // dd($instanceData, $modelTypeSchema, $modelInstance);

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

                $fields[] = $entry->state($value ?? 'infolistSchema - returned');
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
            return 'formatDate - returned';
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
            return 'formatSateTime - returned';
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
            return 'formatTime - returned';
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
            return 'formatSelect - returned';
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
}
