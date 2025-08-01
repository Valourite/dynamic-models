<?php

namespace Valourite\DynamicModels\Filament\Support\Generators;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
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
        $modelType       = $modelType instanceof ModelType ? $modelType : ModelType::findOrFail($modelType);
        $modelTypeSchema = $modelType->model_type_schema ?? [];

        $components = [];

        foreach ($modelTypeSchema as $section) {
            $fields = [];

            foreach ($section['Fields'] ?? [] as $field) {
                $fieldID     = $field['custom_id'] ?? null;
                $type        = $field['type'] ?? 'text';
                $label       = $field['label'] ?? $field['name'] ?? $fieldID;
                $required    = $field['required'] ?? false;
                $prefixIcon  = $field['prefix_icon'] ?? null;
                $suffixIcon  = $field['suffix_icon'] ?? null;
                $prefixColor = $field['prefix_icon_color'] ?? null;
                $suffixColor = $field['suffix_icon_color'] ?? null;

                if ( ! $fieldID) {
                    continue;
                }

                $component = static::getRenderedFieldComponent($type, $fieldID);

                $component
                    ->label($label)
                    ->required($required)
                    ->afterStateHydrated(
                        fn (Component $component, $state) => static::hydrateResponseState($component, $fieldID)
                    );

                // Handle icons
                if ($prefixIcon && static::hasMethod($component, 'prefixIcon')) {
                    $component->prefixIcon(Heroicon::from($prefixIcon));

                    if ($prefixColor && static::hasMethod($component, 'prefixIconColor')) {
                        $component->prefixIconColor($prefixColor);
                    }
                }

                if ($suffixIcon && static::hasMethod($component, 'suffixIcon')) {
                    $component->suffixIcon(Heroicon::from($suffixIcon));

                    if ($suffixColor && static::hasMethod($component, 'suffixIconColor')) {
                        $component->suffixIconColor($suffixColor);
                    }
                }

                // Handle options
                if (static::hasMethod($component, 'options') && ! empty($field['options'])) {
                    $component->options(
                        collect($field['options'])->mapWithKeys(fn ($opt) => [
                            $opt['value'] => Str::title(str_replace('_', ' ', $opt['label'])),
                        ])->toArray()
                    );
                }

                // Apply additional settings from field data (excluding already handled keys)
                $handled = [
                    'name',
                    'label',
                    'type',
                    'required',
                    'custom_id',
                    'prefix_icon',
                    'suffix_icon',
                    'prefix_icon_color',
                    'suffix_icon_color',
                    'max_length',
                    'options',
                ];

                foreach ($field as $key => $value) {
                    if (in_array($key, $handled)) {
                        continue;
                    }

                    $method = Str::camel($key);

                    if (method_exists($component, $method)) {
                        $component->{$method}($value);
                    } elseif (method_exists($component, 'extraAttributes')) {
                        $component->extraAttributes([$key => $value]);
                    }
                }

                $fields[] = $component;
            }

            if ( ! empty($fields)) {
                $sectionTitle     = $section['title'] ?? 'Section';
                $sectionComponent = Section::make($sectionTitle)
                    ->schema($fields);

                if ( ! empty($section['helper_text'])) {
                    $sectionComponent->description($section['helper_text']);
                }

                if ( ! empty($section['column_count'])) {
                    $sectionComponent->columns((int) $section['column_count']);
                }

                if ( ! empty($section['is_collapsible'])) {
                    $sectionComponent->collapsible();
                }

                if ( ! empty($section['column_span_full'])) {
                    $sectionComponent->columnSpanFull();
                }

                $components[] = $sectionComponent;
            }
        }

        return $components;
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
        $instanceData    = $modelInstance?->modelInstanceValues->pluck(ModelInstanceValue::VALUE, ModelInstanceValue::FIELD_ID);

        $entries = [];

        foreach ($modelTypeSchema as $section) {
            $sectionTitle = $section['title'] ?? 'Section';
            $fields       = [];

            foreach ($section['Fields'] ?? [] as $field) {
                $fieldId = $field['custom_id'] ?? null;
                if ( ! $fieldId) {
                    continue;
                }

                $label = $field['label'] ?? $field['name'] ?? 'Field';
                $value = $instanceData[$fieldId] ?? '-';

                $value = match ($field['type']) {
                    'boolean' => $value ? 'Yes' : 'No',
                    'date'    => static::formatDate($value),
                    default   => $value,
                };

                $fields[] = TextEntry::make($fieldId)
                    ->label($label)
                    ->state($value);
            }

            if ( ! empty($fields)) {
                $sectionComponent = Section::make($sectionTitle)
                    ->schema($fields);

                // Column count
                if ( ! empty($section['column_count'])) {
                    $sectionComponent->columns((int) $section['column_count']);
                }

                // Full width
                if ( ! empty($section['column_span_full'])) {
                    $sectionComponent->columnSpanFull();
                }

                // Collapsible
                if ( ! empty($section['is_collapsible'])) {
                    $sectionComponent->collapsible();
                }

                // Description (helper text)
                if ( ! empty($section['helper_text'])) {
                    $sectionComponent->description($section['helper_text']);
                }

                $entries[] = $sectionComponent;
            }
        }

        return $entries;
    }

    private static function formatDate($value): string
    {
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            return '-';
        }
    }

    private static function hasMethod(Component $component, string $method): bool
    {
        return method_exists($component, $method);
    }

    private static function getRenderedFieldComponent(string $type, string $fieldID): Component
    {
        $cacheKey = "{$type}:{$fieldID}";

        return static::$componentCache[$cacheKey] ??= FieldRenderer::render($type, $fieldID);
    }

    private static function hydrateResponseState(Component $component, string $fieldID): void
    {
        $record   = $component->getLivewire()?->record;
        $instance = $record?->modelInstance;
        $values   = $instance?->modelInstanceValues->pluck(ModelInstanceValue::VALUE, ModelInstanceValue::FIELD_ID);

        $value = $values[$fieldID] ?? null;

        $component->state($value);
    }
}
