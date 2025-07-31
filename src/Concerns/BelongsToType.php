<?php

namespace Valourite\DynamicModels\Concerns;

use Valourite\DynamicModels\Models\ModelType;

//TODO: docblock not picking up?
/**
 * @propety Form::class $form
 */
trait BelongsToType
{
    /**
     * Returns the form this response belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function modelType()
    {
        return $this->belongsTo(ModelType::class, ModelType::PRIMARY_KEY);
    }
}
