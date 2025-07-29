<?php

namespace Valourite\FormBuilder\Concerns;

use Valourite\FormBuilder\Models\Form;
use Valourite\FormBuilder\Models\FormResponse;

trait HasResponse
{    
    /**
     * Returns the form response that is linked to this model
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function response()
    {
        return $this->morphOne(FormResponse::class, FormResponse::MORPH_NAME);
    }

    /**
     * Returns the form this model uses through the form response
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function form()
    {
        return $this->hasOneThrough(
            Form::class,
            FormResponse::class,
            FormResponse::MODEL_ID,
            Form::FORM_ID,
        )->where(FormResponse::MODEL_TYPE, static::class);
    }

    protected static function booted(): void
    {
        /**
         * Deletes all responses attached to the model
         */
        static::deleting(function ($model) {
            $model->response()->delete();
        });

        /**
         * Eager loads the response and form
         */
        //TODO: Implement this --> right now it breaks the server
        // static::addGlobalScope('withResponseAndForm', function ($builder) {
        //     $builder->with('response.form');
        // });
    }
}