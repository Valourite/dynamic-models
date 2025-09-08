<?php

namespace Valourite\DynamicModels\Filament\Support\Generators;

use Carbon\Carbon;
use DateTimeInterface;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $modelType = $modelType instanceof ModelType ? $modelType : ModelType::findOrFail($modelType);
        $modelTypeSchema = $modelType->model_type_schema ?? [];

        $components = [];

        foreach ($modelTypeSchema as $section) {
            $fields = [];

            foreach ($section['Fields'] ?? [] as $field) {
                $fieldID = $field['custom_id'] ?? null;
                $type = $field['type'] ?? 'text';

                if (!$fieldID) {
                    continue;
                }

                //test if type is a file and context is edit
                if ($type === 'file' && ($context === 'edit' || $context === 'display')) {
                    $description = $context === 'edit'
                        ? 'File uploads cannot be changed after creation.'
                        : 'This field would be used to upload a file.';

                    $icon = $context === 'edit'
                        ? Heroicon::OutlinedExclamationCircle
                        : Heroicon::OutlinedQuestionMarkCircle;

                    //create component to act as a placeholder file component
                    $component = Section::make($field['label'] ?? 'File Upload')
                        ->description($description)
                        ->icon($icon);

                    //add component to fields and skip rest of loop
                    $fields[] = $component;
                    continue;
                }

                // Create the component with all field options
                $component = FieldRenderer::render($type, $fieldID, $field);

                // Add afterStateHydrated hook to set the value from modelInstanceValues when in edit mode
                $component->afterStateHydrated(function ($state, $component) use ($fieldID, $type) {
                    // Resolve Livewire and record robustly across containers
                    $livewire = $component->getLivewire()
                        ?? ($component->getContainer()->getLivewire() ?? ($component->getContainer()->getParentComponent()?->getLivewire() ?? null));
                    $record = $livewire
                        ? (method_exists($livewire, 'getRecord') ? $livewire->getRecord() : ($livewire->record ?? null))
                        : null;

                    if (!$record || !method_exists($record, 'modelInstance') || !$record->modelInstance) {
                        return;
                    }

                    $instanceValues = $record->modelInstance->modelInstanceValues->pluck('value', 'field_id');
                    $value = $instanceValues[$fieldID] ?? null;

                    if ($value === null) {
                        return;
                    }

                    // For native inputs, normalize to strings in HTML-expected formats
                    if (in_array($type, ['date', 'datetime', 'time'], true)) {
                        if ($value instanceof \Carbon\CarbonInterface || $value instanceof DateTimeInterface) {
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
                $sectionTitle = Str::title($section['title']) ?? 'Section';
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
            } else {
                //section is empty, display a placeholder
                $sectionComponent = Section::make($section['title'] ?? 'Section')
                    ->description('Fields defined here have been hidden as they can no longer be edited.')
                    // ->color('gray')
                    ->icon('heroicon-o-exclamation-circle')
                    ->schema([]);

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

                //if type is file, we skip
                if ($type === 'file') {
                    // $value can be JSON or a string — normalize to array.
                    $items = is_array($value) ? $value : json_decode($value, true);
                    if (!is_array($items)) {
                        $items = array_filter([$value]);
                    }

                    $disk = $field['disk'] ?? config('dynamic-models.uploads.disk', 'public');
                    $visibility = $field['visibility'] ?? config('dynamic-models.uploads.visibility', 'public');
                    $directory = trim($field['directory'] ?? config('dynamic-models.uploads.directory', ''), '/');

                    foreach ($items as $idx => $item) {
                        $item = (string) $item;

                        $isUrl = Str::startsWith($item, ['http://', 'https://']);
                        $isPublicLink = Str::startsWith($item, ['/storage/']);

                        $relative = ltrim($item, '/');
                        if (!$isUrl && !$isPublicLink) {
                            if ($directory !== '' && !Str::startsWith($relative, $directory . '/')) {
                                $relative = $directory . '/' . $relative;
                            }
                        }

                        $resolveOpenUrl = function (string $disk, string $path) {
                            $diskCfg = config("filesystems.disks.$disk", []);
                            $driver = Arr::get($diskCfg, 'driver');

                            // If disk has a base URL (e.g. public disk), use it
                            if ($urlBase = Arr::get($diskCfg, 'url')) {
                                return rtrim($urlBase, '/') . '/' . ltrim($path, '/');
                            }

                            // S3 or other cloud that supports temporaryUrl
                            if (method_exists(Storage::disk($disk), 'temporaryUrl') && in_array($driver, ['s3'])) {
                                return Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(15));
                            }

                            return route('files.show', [
                                'disk' => $disk,
                                'path' => $path,
                            ]);
                        };

                        $entry = ImageEntry::make("file_preview_{$idx}")
                            ->label($label)
                            ->height(180)
                            ->extraAttributes(['class' => 'rounded-xl shadow'])
                            ->url(function ($state) use ($isUrl, $isPublicLink, $disk, $resolveOpenUrl) {
                                if ($isUrl || $isPublicLink) {
                                    return $state;
                                }
                                return $resolveOpenUrl($disk, $state);
                            })
                            ->openUrlInNewTab();

                        if ($isUrl || $isPublicLink) {
                            $entry->getStateUsing(fn() => $item);
                        } else {
                            $entry->disk($disk)
                                ->visibility($visibility)
                                ->getStateUsing(fn() => $relative);
                        }

                        $fields[] = $entry;
                    }

                    // continue to next field
                    continue;
                }

                // Create a text entry with the formatted value
                $entry = TextEntry::make($fieldId)
                    ->label($label);

                // Apply different formatters based on field type
                if ($type === 'date') {
                    $format = $field['display_format'] ?? 'Y-m-d';
                    $entry->date($format);
                } elseif ($type === 'datetime') {
                    $format = $field['display_format'] ?? 'Y-m-d H:i:s';
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
