<?php

namespace Valourite\DynamicModels\Concerns;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Valourite\DynamicModels\Models\ModelInstance;
use Valourite\DynamicModels\Models\ModelInstanceValue;
use Valourite\DynamicModels\Models\ModelType;

trait IsDynamic
{
    /**
     * Returns the model instance that is linked to this model.
     *
     * @return MorphOne
     */
    public function modelInstance()
    {
        return $this->morphOne(ModelInstance::class, ModelInstance::MORPH_NAME);
    }

    /**
     * Returns the model type this model uses through the model instance.
     *
     * @return HasOneThrough
     */
    public function modelType(): HasOneThrough
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

    /**
     * Returns all the model instance values this model has
     * Essentially returning the values that this model set on creation with a type.
     *
     * @return HasManyThrough
     */
    public function modelInstanceValues()
    {
        return $this->hasManyThrough(
            ModelInstanceValue::class,
            ModelInstance::class,
            ModelInstance::PARENT_MODEL_ID,
            ModelInstanceValue::MODEL_INSTANCE_ID,
            'id',
            ModelInstance::MODEL_INSTANCE_ID
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
