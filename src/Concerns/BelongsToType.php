<?php

namespace Valourite\DynamicModels\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * @return BelongsTo
     */
    public function modelType()
    {
        return $this->belongsTo(ModelType::class, ModelType::PRIMARY_KEY);
    }
}
