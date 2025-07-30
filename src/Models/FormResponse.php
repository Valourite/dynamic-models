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
    public const FORM_RESPONSE_ID = 'form_response_id';

    public const FORM_ID = 'form_id';

    public const MODEL_ID = 'model_id';

    public const MODEL_TYPE = 'model_type';

    public const RESPONSE_DATA = 'response_data';

    public const PRIMARY_KEY = 'form_response_id';

    public const MORPH_NAME = 'model';

    public const BASE_TABLE_NAME = 'form_responses';

    /**
     * =========================
     *		 FIELDS
     * =========================.
     */
    public $incrementing = true;

    protected $primaryKey = self::PRIMARY_KEY;

    protected $table;

    protected $dateFormat = 'Y-m-d';

    /**
     * =========================
     *		 CASTS
     * =========================.
     */
    protected $casts = [
        self::FORM_ID       => 'int',
        self::MODEL_ID      => 'int',
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
     * =========================
     * 		 WITH
     * ========================.
     */
    protected $with = ['model', 'form'];

    /**
     * =========================
     * 		 CONSTRUCTOR
     * ========================.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('form-builder.table_prefix') . self::BASE_TABLE_NAME);
    }

    /*
     * =========================
     *		 RELATIONS
     * =========================
     */

    /**
     * Returns the model this response belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }
}
