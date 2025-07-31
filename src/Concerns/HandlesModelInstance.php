<?php

namespace Valourite\DynamicModels\Concerns;

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
                fn($_, $key) => $key === 'model_type_id' ||
                str_starts_with($key, 'field-')
            )
            ->all();
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
        if (!method_exists($this->record, 'modelInstance') || !method_exists($this->record, 'modelType')) {
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

        $values = [];

        foreach ($modelTypeSchema as $section) {
            foreach ($section['Fields'] ?? [] as $field) {
                $customId = $field['custom_id'] ?? null;

                if ($customId && array_key_exists($customId, $this->dynamicModelRawData)) {
                    $values[] = [
                        ModelInstanceValue::NAME => $field['name'],
                        ModelInstanceValue::FIELD_ID => $customId,
                        ModelInstanceValue::VALUE => $this->dynamicModelRawData[$customId],
                        ModelInstanceValue::TYPE => $field['type'],
                        ModelInstanceValue::CREATED_AT => now(),
                        ModelInstanceValue::UPDATED_AT => now(),
                    ];
                }
            }
        }

        /** @var Model $model */
        $model = $this->record;

        $modelInstance = $model->modelInstance()->updateOrCreate([], [
            ModelInstance::MODEL_TYPE_ID => $modelTypeID,
            ModelInstance::PARENT_MODEL_TYPE => get_class($model),
            ModelInstance::PARENT_MODEL_ID => $model->getKey(),
        ]);

        // Attach model_instance_id to each row
        foreach ($values as &$row) {
            $row[ModelInstance::MODEL_INSTANCE_ID] = $modelInstance->getKey();
        }

        // Perform bulk upsert
        ModelInstanceValue::upsert(
            $values,
            [ModelInstanceValue::MODEL_INSTANCE_ID, ModelInstanceValue::FIELD_ID], // Unique constraint
            [ModelInstanceValue::NAME, ModelInstanceValue::VALUE, ModelInstanceValue::TYPE, ModelInstanceValue::UPDATED_AT]     // Columns to update
        );
    }

}
