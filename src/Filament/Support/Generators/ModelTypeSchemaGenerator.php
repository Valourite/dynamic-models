<?php

namespace Valourite\DynamicModels\Filament\Support\Generators;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Throwable;
use Valourite\DynamicModels\Filament\Support\Renderers\FieldRenderer;
use Valourite\DynamicModels\Models\ModelType;
use Valourite\DynamicModels\Models\ModelInstance;

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
        $modelType        = $modelType instanceof ModelType ? $modelType : ModelType::findOrFail($modelType);
        $modelTypeSchema = $modelType->model_type_schema ?? [];

        $components = [];

        foreach ($modelTypeSchema as $section) {
            $fields = [];

            foreach ($section['Fields'] ?? [] as $field) {
                $fieldID = $field['custom_id'] ?? null;
                if ( ! $fieldID) {
                    continue;
                }

                $name       = $field['name'] ?? $fieldID;
                $label      = $field['label'] ?? Str::title($name);
                $type       = $field['type'] ?? 'text';
                $required   = $field['required'] ?? false;
                $prefixIcon = $field['prefix_icon'] ?? null;

                // Cache FieldRenderer result per field key per request
                $component = static::getRenderedFieldComponent($type, $fieldID);

                $component
                    ->label($label)
                    ->required($required)
                    ->afterStateHydrated(fn (Component $component, $state) => static::hydrateResponseState($component, $fieldID));

                if ($prefixIcon && static::hasMethod($component, 'prefixIcon')) {
                    $component->prefixIcon(Heroicon::from($prefixIcon));

                    if (static::hasMethod($component, 'prefixIconColor')) {
                        $component->prefixIconColor('white');
                    }
                }

                if (static::hasMethod($component, 'options') && ! empty($field['options'])) {
                    $component->options(
                        collect($field['options'])->mapWithKeys(fn ($opt) => [
                            $opt['value'] => Str::title(str_replace('_', ' ', $opt['label'])),
                        ])->toArray()
                    );
                }

                $fields[] = $component;
            }

            if ( ! empty($fields)) {
                $components[] = Section::make($section['title'] ?? 'Section')
                    ->schema($fields)
                    ->collapsible();
            }
        }

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

        $modelTypeSchema  = $modelInstance->modelType?->model_type_schema ?? [];
        $instanceData = $modelInstance->model_instance_data ?? [];

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
                $entries[] = Section::make($sectionTitle)
                    ->schema($fields)
                    ->columns(2);
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

        if ($instance?->model_instance_data) {
            $component->state($instance->model_instance_data[$fieldID] ?? null);
        }
    }
}
