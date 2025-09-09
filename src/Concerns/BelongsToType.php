<?php

namespace Valourite\DynamicModels\Concerns;

use Valourite\DynamicModels\Models\ModelType;

trait BelongsToType
{
    /**
     * Returns the form this response belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Builder<ModelType>
     */
    public function modelType()
    {
        return $this->belongsTo(ModelType::class, ModelType::PRIMARY_KEY)->withTrashed();
    }
}
