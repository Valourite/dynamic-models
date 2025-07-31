<?php

namespace Valourite\DynamicModels\Concerns;

use Valourite\DynamicModels\Models\ModelType;
use Valourite\DynamicModels\Models\ModelInstance;

trait IsDynamic
{
    /**
     * Returns the model instance that is linked to this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function modelInstance()
    {
        return $this->morphOne(ModelInstance::class, ModelInstance::MORPH_NAME);
    }

    /**
     * Returns the model type this model uses through the model instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function modelType(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            ModelType::class,
            ModelInstance::class,
            ModelInstance::PARENT_MODEL_ID,
            ModelType::MODEL_TYPE_ID,
            'id',
            ModelInstance::MODEL_TYPE_ID
        )->where(ModelInstance::PARENT_MODEL_TYPE, static::class);
    }

    protected static function booted(): void
    {
        /*
         * Deletes model instance attached to this model
         */
        static::deleting(function ($model) {
            $model->modelInstance()->delete();
        });
    }
}
