<?php

namespace Valourite\FormBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Valourite\FormBuilder\Concerns\UsesForm;

final class FormResponse extends Model
{
    /**
     * =========================
     *		 TRAIT
     * =========================.
     */
    use UsesForm;

    /**
     * ==========================
     *		 CONSTANTS
     * ==========================.
     */

    const FORM_RESPONSE_ID = 'form_response_id';

    const FORM_ID = 'form_id';
    
    const MODEL_ID = 'model_id';
    
    const MODEL_TYPE = 'model_type';
    
    const RESPONSE_DATA = 'response_data';
    
    const PRIMATY_KEY = 'form_response_id';

    /**
     * =========================
     *		 FIELDS
     * =========================.
     */
    
    public $incrementing = true;

    // protected static string $tableName;

    protected $table;

    protected $primaryKey = self::PRIMARY_KEY;

    protected $dateFormat = 'Y-m-d';

    /**
     * =========================
     *		 CASTS
     * =========================.
     */

    protected $casts = [
        self::FORM_ID => 'int',
        self::MODEL_ID => 'int',
        self::RESPONSE_DATA => 'json',
    ];

    /**
     * =========================
     *		 FILLABLE
     * =========================.
     */

    protected $fillable = [
        self::FORM_ID,
        self::MODEL_ID,
        self::MODEL_TYPE,
        self::RESPONSE_DATA,
    ];

    /**
     * =======================
     *      BOOTED
     * =======================.
     */
    public static function booted(): void
    {
        self::$table = config('form-builder.table_prefix') . 'forms';

        // static::$tableName = config('form-builder.table_prefix') . 'forms';
    }

    /*
     * =========================
     *		 RELATIONS
     * =========================
     */

    /**
     * Returns the model this response belongs to
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * ========================
     * 		FILAMENT
     * ========================.
     */
    public function getTable()
    {
        return config('form-builder.table_prefix') . 'form_responses';
    }
}
