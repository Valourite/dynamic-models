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
        //set the raw data

        // dd($this->data);

        // Process form data before validation
        // if ($this->form) {
        //     // Process all form components to fix any issues
        //     $components = $this->form->getComponents();
        //     // $this->fixFormComponentOptions($components);


        //     // Process date and select fields
        //     $this->processDateFields();

        // }
    }

    /**
     * Process date fields to ensure they are properly formatted before validation.
     */
    // protected function processDateFields(): void
    // {
    //     foreach ($this->data as $key => $value) {
    //         // Skip fields that don't start with 'field-'
    //         if ( ! str_starts_with($key, 'field-')) {
    //             continue;
    //         }

    //         // Get field component and type
    //         $component = $this->form ? $this->form->getComponent($key) : null;
    //         $fieldType = null;

    //         // Determine field type based on component instance
    //         if ($component instanceof DatePicker) {
    //             $fieldType = 'date';
    //         } elseif ($component instanceof DateTimePicker) {
    //             $fieldType = 'datetime';
    //         } elseif ($component instanceof TimePicker) {
    //             $fieldType = 'time';
    //         } elseif ($component instanceof Select && $component->isMultiple()) {
    //             $fieldType = 'select-multiple';
    //         } elseif ($component instanceof Select) {
    //             $fieldType = 'select';
    //         } elseif($component instanceof FileUpload) {
    //             $fieldType = 'file';
    //         }

    //         // Skip if we couldn't determine the field type
    //         if ( ! $fieldType) {
    //             continue;
    //         }

    //         // Process the field based on its type and current value
    //         switch ($fieldType) {
    //             case 'date':
    //                 // Handle Carbon/DateTime objects
    //                 if ($value instanceof Carbon || $value instanceof DateTime) {
    //                     $this->data[$key] = $value->format('Y-m-d');
    //                     // Also ensure the component state is updated
    //                     if ($component) {
    //                         $component->state($value);
    //                     }
    //                 }
    //                 // Handle string dates
    //                 elseif (is_string($value) && ! empty($value)) {
    //                     try {
    //                         $date             = Carbon::parse($value);
    //                         $this->data[$key] = $date->format('Y-m-d');
    //                         // Also ensure the component state is updated
    //                         if ($component) {
    //                             $component->state($date);
    //                         }
    //                     } catch (Exception $e) {
    //                         // If parsing fails, leave as is but ensure not null
    //                         if ($component && empty($component->getState())) {
    //                             // Try to set a default valid date as fallback
    //                             $component->state(now()->startOfDay());
    //                         }
    //                     }
    //                 }
    //                 // Handle null or empty values
    //                 elseif ($value === null || (is_string($value) && empty($value))) {
    //                     // Don't store null dates, use current date as fallback
    //                     // This prevents validation errors for required date fields
    //                     $date             = now()->startOfDay();
    //                     $this->data[$key] = $date->format('Y-m-d');
    //                     if ($component) {
    //                         $component->state($date);
    //                     }
    //                 }
    //                 break;

    //             case 'datetime':
    //                 // Handle Carbon/DateTime objects
    //                 if ($value instanceof Carbon || $value instanceof DateTime) {
    //                     $this->data[$key] = $value->format('Y-m-d H:i:s');
    //                     // Also ensure the component state is updated
    //                     if ($component) {
    //                         $component->state($value);
    //                     }
    //                 }
    //                 // Handle string dates
    //                 elseif (is_string($value) && ! empty($value)) {
    //                     try {
    //                         $date             = Carbon::parse($value);
    //                         $this->data[$key] = $date->format('Y-m-d H:i:s');
    //                         // Also ensure the component state is updated
    //                         if ($component) {
    //                             $component->state($date);
    //                         }
    //                     } catch (Exception $e) {
    //                         // If parsing fails, leave as is but ensure not null
    //                         if ($component && empty($component->getState())) {
    //                             // Try to set a default valid datetime as fallback
    //                             $component->state(now());
    //                         }
    //                     }
    //                 }
    //                 // Handle null or empty values
    //                 elseif ($value === null || (is_string($value) && empty($value))) {
    //                     // Don't store null datetimes, use current time as fallback
    //                     // This prevents validation errors for required datetime fields
    //                     $date             = now();
    //                     $this->data[$key] = $date->format('Y-m-d H:i:s');
    //                     if ($component) {
    //                         $component->state($date);
    //                     }
    //                 }
    //                 break;

    //             case 'time':
    //                 // Handle Carbon/DateTime objects
    //                 if ($value instanceof Carbon || $value instanceof DateTime) {
    //                     $this->data[$key] = $value->format('H:i:s');
    //                     // Also ensure the component state is updated
    //                     if ($component) {
    //                         $component->state($value);
    //                     }
    //                 }
    //                 // Handle string times
    //                 elseif (is_string($value) && ! empty($value)) {
    //                     try {
    //                         $time             = Carbon::parse($value);
    //                         $this->data[$key] = $time->format('H:i:s');
    //                         // Also ensure the component state is updated
    //                         if ($component) {
    //                             $component->state($time);
    //                         }
    //                     } catch (Exception $e) {
    //                         // If parsing fails, leave as is but ensure not null
    //                         if ($component && empty($component->getState())) {
    //                             // Try to set a default valid time as fallback
    //                             $component->state(now());
    //                         }
    //                     }
    //                 }
    //                 // Handle null or empty values
    //                 elseif ($value === null || (is_string($value) && empty($value))) {
    //                     // Don't store null times, use current time as fallback
    //                     // This prevents validation errors for required time fields
    //                     $time             = now();
    //                     $this->data[$key] = $time->format('H:i:s');
    //                     if ($component) {
    //                         $component->state($time);
    //                     }
    //                 }
    //                 break;
    //             case 'file':
    //                 //we need to just store the file location
    //                 $value = "/NEED/TO/SET/FILE/PATH";
    //                 $component->state($value);
    //         }

    //     }
    // }

    // /**
    //  * Recursively fix form component options.
    //  *
    //  * @param array $components
    //  */
    // protected function fixFormComponentOptions(array $components): void
    // {
    //     foreach ($components as $component) {
    //         // Get component ID if available
    //         $componentId    = method_exists($component, 'getName') ? $component->getName() : 'unknown';
    //         $componentClass = get_class($component);


    //         // Handle Select components
    //         // if ($component instanceof Select) {
    //         //     // Ensure all Select components have at least empty options
    //         //     if ($component->getOptions() === null) {
    //         //         $component->options([]);
    //         //     }

    //         //     // For multiple selects, ensure state is always an array
    //         //     if ($component->isMultiple()) {
    //         //         $state = $component->getState();

    //         //         // If state is null or not an array, set it to an empty array
    //         //         if ($state === null || ! is_array($state)) {
    //         //             $component->state([]);
    //         //         }

    //         //         // Ensure all selected values are present in options to avoid validation errors
    //         //         if (is_array($state) && ! empty($state)) {
    //         //             $options = $component->getOptions() ?? [];

    //         //             $filteredState = array_filter(
    //         //                 $state,
    //         //                 fn ($value) => array_key_exists($value, $options)
    //         //             );
    //         //         }
    //         //     } else {
    //         //         // For single select, make sure the selected value exists in options
    //         //         $state   = $component->getState();
    //         //         $options = $component->getOptions() ?? [];

    //         //         if ($state !== null && ! empty($options) && ! array_key_exists($state, $options)) {
    //         //             // If the selected value doesn't exist in options, reset it
    //         //             $component->state(null);
    //         //         }
    //         //     }
    //         // }

    //         // Handle Date/DateTime/Time components
    //         // if ($component instanceof DatePicker ||
    //         //     $component instanceof DateTimePicker ||
    //         //     $component instanceof TimePicker) {
    //         //     // Get current state for debugging
    //         //     $state = $component->getState();

    //         //     // Make sure date fields accept blank values (prevents validation errors)
    //         //     $component->required(false);

    //         //     // Ensure date format is appropriate
    //         //     if ($component instanceof DatePicker) {
    //         //         $component->displayFormat('Y-m-d');
    //         //     } elseif ($component instanceof DateTimePicker) {
    //         //         $component->displayFormat('Y-m-d H:i:s');
    //         //     } elseif ($component instanceof TimePicker) {
    //         //         $component->displayFormat('H:i:s');
    //         //     }

    //         //     // Fix date values if needed
    //         //     if ($state !== null) {
    //         //         // If state is a string but should be a Carbon instance
    //         //         if (is_string($state) && ! empty($state)) {
    //         //             try {
    //         //                 if ($component instanceof DatePicker) {
    //         //                     $carbon = Carbon::parse($state)->startOfDay();
    //         //                     $component->state($carbon);
    //         //                 } elseif ($component instanceof DateTimePicker) {
    //         //                     $carbon = Carbon::parse($state);
    //         //                     $component->state($carbon);
    //         //                 } elseif ($component instanceof TimePicker) {
    //         //                     $carbon = Carbon::parse($state);
    //         //                     $component->state($carbon);
    //         //                 }
    //         //             } catch (Exception $e) {
    //         //                 // Set default values for invalid dates
    //         //                 if ($component instanceof DatePicker) {
    //         //                     $component->state(now()->startOfDay());
    //         //                 } elseif ($component instanceof DateTimePicker) {
    //         //                     $component->state(now());
    //         //                 } elseif ($component instanceof TimePicker) {
    //         //                     $component->state(now());
    //         //                 }
    //         //             }
    //         //         }
    //         //     } else {
    //         //         // Handle null state by setting default values
    //         //         if ($component instanceof DatePicker) {
    //         //             $component->state(now()->startOfDay());
    //         //         } elseif ($component instanceof DateTimePicker) {
    //         //             $component->state(now());
    //         //         } elseif ($component instanceof TimePicker) {
    //         //             $component->state(now());
    //         //         }
    //         //     }
    //         // }

    //         // Process child components recursively
    //         if (method_exists($component, 'getChildComponents')) {
    //             $this->fixFormComponentOptions($component->getChildComponents());
    //         }

    //         // Process child schemas recursively
    //         if (method_exists($component, 'getChildSchemas')) {
    //             foreach ($component->getChildSchemas() as $childSchema) {
    //                 $this->fixFormComponentOptions($childSchema->getComponents());
    //             }
    //         }
    //     }
    // }

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
                // if ($value !== null) {
                //     // Handle arrays (for multiple select)
                //     if (($value instanceof Carbon || $value instanceof DateTime)) {
                //         if ($fieldType === 'date') {
                //             $value = $value->format('Y-m-d');
                //         } elseif ($fieldType === 'datetime') {
                //             $value = $value->format('Y-m-d H:i:s');
                //         } elseif ($fieldType === 'time') {
                //             $value = $value->format('H:i:s');
                //         }
                //     }
                //     // Handle string dates that need formatting
                //     elseif (is_string($value) && in_array($fieldType, ['date', 'datetime', 'time'])) {
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

        // dd($values);

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
