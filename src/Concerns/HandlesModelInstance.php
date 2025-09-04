<?php

namespace Valourite\DynamicModels\Concerns;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Valourite\DynamicModels\Models\ModelInstance;
use Valourite\DynamicModels\Models\ModelInstanceValue;
use Valourite\DynamicModels\Models\ModelType;

trait HandlesModelInstance
{
    public function afterValidate()
    {
        $this->dynamicModelRawData = $this->data;

        // Reject dynamic-models fields
        $this->data = collect($this->data)
            ->reject(
                fn ($_, $key) => $key === 'model_type_id' ||
                str_starts_with($key, 'field-')
            )
            ->all();

    }

    public function beforeValidate()
    {
        // dd($this->data, $this->form);
        // Process form data before validation
        if ($this->form) {
            // Process date fields to ensure they have values
            $this->processDateFields();
        }
    }

    /**
     * Process date fields to ensure they are properly formatted before validation.
     */
    protected function processDateFields(): void
    {
        // Skip if we don't have a form
        if (!$this->form) {
            return;
        }

        $components = $this->form->getFlatComponents();

        foreach ($components as $component) {

            // Skip if component isn't a date/time field
            if (!($component instanceof DatePicker || $component instanceof DateTimePicker || $component instanceof TimePicker)) {
                continue;
            }

            $key = $component->getName();
            $value = $this->data[$key] ?? null;

            // Skip if already has a value
            if (!empty($value)) {
                continue;
            }

            // // Provide a default value
            // $now = now();
            
            // if ($component instanceof DatePicker) {
            //     $this->data[$key] = $now->format('Y-m-d');
            //     $component->state($now);
            // } elseif ($component instanceof DateTimePicker) {
            //     $this->data[$key] = $now->format('Y-m-d H:i:s');
            //     $component->state($now);
            // } elseif ($component instanceof TimePicker) {
            //     $this->data[$key] = $now->format('H:i:s');
            //     $component->state($now);
            // }
        }
    }

    protected function afterSave(): void
    {
        $this->createOrUpdateModelInstance();

        //fill the form after save to remount the data
        $this->fillForm();
    }

    protected function afterCreate(): void
    {
        $this->createOrUpdateModelInstance();
    }

    protected function createOrUpdateModelInstance(): void
    {
        if (!method_exists($this->record, 'modelInstance') || ! method_exists($this->record, 'modelType')) {
            return;
        }

        $modelTypeID = $this->dynamicModelRawData['model_type_id'] ?? null;
        if (!$modelTypeID) {
            return;
        }

        $modelType = ModelType::find($modelTypeID);
        if (!$modelType) {
            return;
        }

        $modelTypeSchema = $modelType->model_type_schema ?? [];

        $values   = [];


        foreach ($modelTypeSchema as $sectionIndex => $section) {

            foreach ($section['Fields'] ?? [] as $fieldIndex => $field) {
                $customId  = $field['custom_id'] ?? null;
                $fieldType = $field['type'] ?? null;


                // Get the raw value from the form data
                $value = $this->dynamicModelRawData[$customId] ?? null;

                if (!$field) {
                    continue;
                }

                // Double check field type from the schema
                $fieldType = $field['type'] ?? null;
                if (!$fieldType) {
                    continue;
                }

                // Process value based on field type
                if ($value !== null) {
                    // Handle arrays (for multiple select)
                    if (is_array($value)) {
                        // Convert to JSON for storage
                        $value = json_encode($value);
                    }
                    // Handle date/time objects
                    elseif (($value instanceof Carbon || $value instanceof DateTime)) {
                        if ($fieldType === 'date') {
                            $value = $value->format('Y-m-d');
                        } elseif ($fieldType === 'datetime') {
                            $value = $value->format('Y-m-d H:i:s');
                        } elseif ($fieldType === 'time') {
                            $value = $value->format('H:i:s');
                        }
                    }
                    // Handle string dates that need formatting
                    elseif (is_string($value) && in_array($fieldType, ['date', 'datetime', 'time'])) {
                        try {
                            $date = Carbon::parse($value);
                            if ($fieldType === 'date') {
                                $value = $date->format('Y-m-d');
                            } elseif ($fieldType === 'datetime') {
                                $value = $date->format('Y-m-d H:i:s');
                            } elseif ($fieldType === 'time') {
                                $value = $date->format('H:i:s');
                            }
                        } catch (Exception $e) {
                            // Use current date/time as fallback for empty/invalid dates
                            if ($fieldType === 'date') {
                                $value = now()->format('Y-m-d');
                            } elseif ($fieldType === 'datetime') {
                                $value = now()->format('Y-m-d H:i:s');
                            } elseif ($fieldType === 'time') {
                                $value = now()->format('H:i:s');
                            }
                        }
                    }
                } else {
                    // For date fields, use current date if value is null
                    if (in_array($fieldType, ['date', 'datetime', 'time'])) {
                        // Use current date/time as fallback for null dates
                        if ($fieldType === 'date') {
                            $value = now()->format('Y-m-d');
                        } elseif ($fieldType === 'datetime') {
                            $value = now()->format('Y-m-d H:i:s');
                        } elseif ($fieldType === 'time') {
                            $value = now()->format('H:i:s');
                        }
                    }
                }
                //         try {
                //             $date = Carbon::parse($value);
                //             if ($fieldType === 'date') {
                //                 $value = $date->format('Y-m-d');
                //             } elseif ($fieldType === 'datetime') {
                //                 $value = $date->format('Y-m-d H:i:s');
                //             } elseif ($fieldType === 'time') {
                //                 $value = $date->format('H:i:s');
                //             }
                //         } catch (Exception $e) {

                //             // Use current date/time as fallback for empty/invalid dates
                //             if ($fieldType === 'date') {
                //                 $value = now()->format('Y-m-d');
                //             } elseif ($fieldType === 'datetime') {
                //                 $value = now()->format('Y-m-d H:i:s');
                //             } elseif ($fieldType === 'time') {
                //                 $value = now()->format('H:i:s');
                //             }
                //         }
                //     }
                // } else {
                //     // Handle null values for date fields
                //     if (in_array($fieldType, ['date', 'datetime', 'time'])) {
                //         // Use current date/time as fallback for null dates
                //         if ($fieldType === 'date') {
                //             $value = now()->format('Y-m-d');
                //         } elseif ($fieldType === 'datetime') {
                //             $value = now()->format('Y-m-d H:i:s');
                //         } elseif ($fieldType === 'time') {
                //             $value = now()->format('H:i:s');
                //         }
                //     }
                // }

                // Prepare the value for storage
                $values[] = [
                    ModelInstanceValue::NAME       => $field['name'],
                    ModelInstanceValue::FIELD_ID   => $customId,
                    ModelInstanceValue::VALUE      => $value,
                    ModelInstanceValue::TYPE       => $fieldType,
                    ModelInstanceValue::CREATED_AT => now(),
                    ModelInstanceValue::UPDATED_AT => now(),
                ];
            }
        }

        /** @var Model $model */
        $model = $this->record;

        $modelInstance = $model->modelInstance()->updateOrCreate([], [
            ModelInstance::MODEL_TYPE_ID     => $modelTypeID,
            ModelInstance::PARENT_MODEL_TYPE => get_class($model),
            ModelInstance::PARENT_MODEL_ID   => $model->getKey(),
        ]);

        // Attach model_instance_id to each row
        foreach ($values as &$row) {
            $row[ModelInstance::MODEL_INSTANCE_ID] = $modelInstance->getKey();
        }

        // Perform bulk upsert
        ModelInstanceValue::upsert(
            $values,
            [ModelInstanceValue::MODEL_INSTANCE_ID, ModelInstanceValue::FIELD_ID], // Unique constraint
            [ModelInstanceValue::NAME, ModelInstanceValue::VALUE, ModelInstanceValue::TYPE, ModelInstanceValue::UPDATED_AT] // Columns to update
        );
    }
}
