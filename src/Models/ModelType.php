<?php

namespace Valourite\DynamicModels\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Valourite\DynamicModels\Database\Factories\ModelTypeFactory;

final class ModelType extends Model
{
    /**
     * =========================
     *		 TRAIT
     * =========================.
     */
    use HasFactory;

    // --------------------------

    /**
     * ==========================
     *		 CONSTANTS
     * ==========================.
     */
    const MODEL_TYPE_ID = 'model_type_id';

    const MODEL_TYPE_NAME = 'model_type_name';

    const MODEL_TYPE_DESCRIPTION = 'model_type_description';

    const MODEL_TYPE_CONFIRMATION_MESSAGE = 'model_type_confirmation_message';

    const CAN_BE_CREATED = 'can_be_created';

    const MODEL_TYPE_PARENT_MODEL = 'model_type_parent_model';

    const MODEL_TYPE_SCHEMA = 'model_type_schema';

    const MODEL_TYPE_VERSION = 'model_type_version';

    const PRIMARY_KEY = 'model_type_id';

    const BASE_TABLE_NAME = 'model_types';

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
        self::CAN_BE_CREATED    => 'boolean',
        self::MODEL_TYPE_SCHEMA => 'json',
    ];

    /**
     * =========================
     *		 FILLABLE
     * =========================.
     */
    protected $fillable = [
        self::MODEL_TYPE_NAME,
        self::MODEL_TYPE_DESCRIPTION,
        self::MODEL_TYPE_CONFIRMATION_MESSAGE,
        self::CAN_BE_CREATED,
        self::MODEL_TYPE_PARENT_MODEL,
        self::MODEL_TYPE_SCHEMA,
        self::MODEL_TYPE_VERSION,
    ];

    /**
     * =========================
     * 		 CONSTRUCTOR
     * ========================.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('dynamic-models.table_prefix') . self::BASE_TABLE_NAME);
    }

    /**
     * =======================
     *      BOOTED
     * =======================.
     */
    public static function booted(): void
    {
        //Prevent an empty schema from being generated
        static::creating(function ($model) {
            if ($model->model_type_schema === null) {
                $model->model_type_schema = json_encode('{}');
            }
        });
    }

    /*
     * =========================
     *		 FACTORY
     * =========================
     */

    public static function factory(): ModelTypeFactory
    {
        return ModelTypeFactory::new();
    }

    /*
     * =========================
     *		 RELATIONS
     * =========================
     */

    public function responses()
    {
        return $this->hasMany(ModelInstance::class, self::PRIMARY_KEY);
    }
}
