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

                // Normalize value for storage
                if (is_array($value)) {
                    // multiple select/file uploads
                    $value = json_encode($value);
                }
                // Normalize date/time values to strings
                elseif ($value instanceof Carbon || $value instanceof DateTime) {
                    $value = match ($fieldType) {
                        'date' => $value->format('Y-m-d'),
                        'datetime' => $value->format('Y-m-d H:i:s'),
                        'time' => $value->format('H:i:s'),
                        default => (string) $value,
                    };
                }

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
