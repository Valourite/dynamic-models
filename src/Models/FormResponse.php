<?php

namespace Valourite\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;

final class FormResponse extends Model
{
    protected $fillable = [
        'form_id',
        'model_id',
        'model_type',
        'response_data',
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
