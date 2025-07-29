<?php

namespace Valourite\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;

final class FormResponse extends Model
{
        'form_schema',
        'form_version',
    protected $casts = [
        'response_data' => 'json',
        'form_schema' => 'json',
    ];

    protected $casts = [
        'response_data' => 'json',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function model()
    {
        return $this->morphTo();
    }
}
