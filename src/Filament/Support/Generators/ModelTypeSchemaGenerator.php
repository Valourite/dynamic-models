<?php

namespace Valourite\DynamicModels\Filament\Support\Generators;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TimePicker;
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
    /**
     * Generates the form schema that can be appended to the models form.
     *
     * @param int|ModelType $modelType
     *
     * @return array
     */
    public static function formSchema(int|ModelType $modelType, string $context): array
    {
        $modelType       = $modelType instanceof ModelType ? $modelType : ModelType::findOrFail($modelType);
        $modelTypeSchema = $modelType->model_type_schema ?? [];

        $components = [];

        foreach ($modelTypeSchema as $section) {
            $fields = [];

            foreach ($section['Fields'] ?? [] as $field) {
                $fieldID = $field['custom_id'] ?? null;
                $type    = $field['type'] ?? 'text';

                if ( ! $fieldID) {
                    continue;
                }

                // Create the component with all field options
                $component = FieldRenderer::render($type, $fieldID, $field);

                //continue if fileUpload and context is edit
                if($component instanceof FileUpload && $context === 'edit'){
                    continue;
                }

                // Add afterStateHydrated hook to set the value from modelInstanceValues when in edit mode
                $component->afterStateHydrated(function ($state, $component) use ($fieldID, $type) {
                    // Resolve Livewire and record robustly across containers
                    $livewire = $component->getLivewire()
                        ?? ($component->getContainer()->getLivewire() ?? ($component->getContainer()->getParentComponent()?->getLivewire() ?? null));
                    $record = $livewire
                        ? (method_exists($livewire, 'getRecord') ? $livewire->getRecord() : ($livewire->record ?? null))
                        : null;

                    if (! $record || ! method_exists($record, 'modelInstance') || ! $record->modelInstance) {
                        return;
                    }

                    $instanceValues = $record->modelInstance->modelInstanceValues->pluck('value', 'field_id');
                    $value = $instanceValues[$fieldID] ?? null;

                    if ($value === null) {
                        return;
                    }

                    // For native inputs, normalize to strings in HTML-expected formats
                    if (in_array($type, ['date', 'datetime', 'time'], true)) {
                        if ($value instanceof \Carbon\CarbonInterface || $value instanceof \DateTimeInterface) {
                            $value = match ($type) {
                                'date' => $value->format('Y-m-d'),
                                'datetime' => $value->format('Y-m-d H:i:s'),
                                'time' => $value->format('H:i:s'),
                                default => (string) $value,
                            };
                        } elseif (is_string($value)) {
                            try {
                                $dt = Carbon::parse($value);
                                $value = match ($type) {
                                    'date' => $dt->format('Y-m-d'),
                                    'datetime' => $dt->format('Y-m-d H:i:s'),
                                    'time' => $dt->format('H:i:s'),
                                    default => $value,
                                };
                            } catch (Throwable) {
                                // leave as-is if parsing fails
                            }
                        }
                    }

                    $component->state($value);
                });

                $fields[] = $component;
            }

            if (!empty($fields)) {
                $sectionTitle     = $section['title'] ?? 'Section';
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

    /**
     * Generates the infolist schema that can be appended to the models infolist.
     *
     * @param int|ModelInstance $modelInstance
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
                $value = $instanceData[$fieldId] ?? null;
                $type  = $field['type'] ?? 'text';

                //if type is file, we skip
                if($type === 'file') {
                    continue;
                }

                // Create a text entry with the formatted value
                $entry = TextEntry::make($fieldId)
                    ->label($label);

                // Apply different formatters based on field type
                if ($type === 'date') {
                    $format = isset($field['display_format']) ? $field['display_format'] : 'Y-m-d';
                    $entry->date($format);
                } elseif ($type === 'datetime') {
                    $format = isset($field['display_format']) ? $field['display_format'] : 'Y-m-d H:i:s';
                    $entry->dateTime($format);
                } elseif ($type === 'time') {
                    $entry->time();
                } elseif ($type === 'boolean' || $type === 'checkbox') {
                    $entry->badge();
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
}
