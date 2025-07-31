<?php

namespace Valourite\DynamicModels\Concerns;

use Illuminate\Database\Eloquent\Model;
use Valourite\DynamicModels\Models\ModelType;
use Valourite\DynamicModels\Models\ModelInstance;

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
        if ( ! method_exists($this->record, 'modelInstance') || ! method_exists($this->record, 'modelType')) {
            return;
        }

        $modelTypeID = $this->dynamicModelRawData['model_type_id'] ?? null;
        if ( ! $modelTypeID) {
            return;
        }

        $modelType = ModelType::find($modelTypeID);
        if ( ! $modelType) {
            return;
        }

        $modelTypeSchema  = $modelType->model_type_schema ?? [];
        $modelInstanceData = [];

        foreach ($modelTypeSchema as $section) {
            foreach ($section['Fields'] ?? [] as $field) {
                $customId = $field['custom_id'] ?? null;
                if ($customId && array_key_exists($customId, $this->dynamicModelRawData)) {
                    $modelInstanceData[$customId] = $this->dynamicModelRawData[$customId];
                }
            }
        }

        /** @var Model $model */
        $model = $this->record;

        //We need to fetch the model instance and update it instead of creating a new one
        $model->modelInstance()->updateOrCreate([], [
            ModelInstance::MODEL_TYPE_ID        => $modelTypeID,
            ModelInstance::PARENT_MODEL_TYPE    => get_class($model),
            ModelInstance::PARENT_MODEL_ID      => $model->getKey(),
            ModelInstance::MODEL_INSTANCE_DATA  => $modelInstanceData,
        ]);
    }
}
