<?php

namespace Valourite\FormBuilder\Concerns;

use Valourite\FormBuilder\Models\Form;

//TODO: docblock not picking up?
/**
 * @propety Form::class $form
 */
trait UsesForm
{
    /**
     * Returns the form this response belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function form()
    {
        return $this->belongsTo(Form::class, Form::PRIMARY_KEY);
    }
}
