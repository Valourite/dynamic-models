<?php

namespace Valourite\DynamicModels\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Valourite\DynamicModels\Models\ModelInstance;

/**
 * This trait will be used to link a model to a model instance.
 */
trait BelongsToModelInstance
{
    /**
     * Returns the model instance this model belongs to.
     *
     * @return BelongsTo
     */
    public function modelInstance()
    {
        return $this->belongsTo(ModelInstance::class, ModelInstance::MODEL_INSTANCE_ID);
    }

    //NOTE: we can add any other functionality or scopes in here 
    // as this trait is specific to the model instance value model only
}
