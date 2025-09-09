<?php

namespace Valourite\DynamicModels\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Valourite\DynamicModels\Database\Factories\ModelTypeFactory;

final class ModelType extends Model
{
    /**
     * =========================
     *		 TRAIT
     * =========================.
     */
    use HasFactory;
    use SoftDeletes;

    // --------------------------

    /**
     * ==========================
     *		 CONSTANTS
     * ==========================.
     */
    public const MODEL_TYPE_ID = 'model_type_id';

    public const PARENT_ID = 'parent_id';

    public const MODEL_TYPE_NAME = 'model_type_name';

    public const MODEL_TYPE_DESCRIPTION = 'model_type_description';

    public const MODEL_TYPE_CONFIRMATION_MESSAGE = 'model_type_confirmation_message';

    public const CAN_BE_CREATED = 'can_be_created';

    public const MODEL_TYPE_PARENT_MODEL = 'model_type_parent_model';

    public const MODEL_TYPE_SCHEMA = 'model_type_schema';

    public const MODEL_TYPE_VERSION = 'model_type_version';

    public const PRIMARY_KEY = 'model_type_id';

    public const BASE_TABLE_NAME = 'model_types';

    /**
     * =========================
     *		 FIELDS
     * =========================.
     */
    public $incrementing = true;

    public $timestamps = true;

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
        self::PARENT_ID,
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

    /**
     * Both parent and children relationships are used for versioning only
     * The end user will not know about these relationships
     * The developer shouldn't need to know of these relationships.
     */
    public function parent()
    {
        return $this->belongsTo(self::class, self::PARENT_ID);
    }

    public function children()
    {
        return $this->hasMany(self::class, self::PARENT_ID);
    }

    /**
     * =========================
     *    SCOPES
     * ========================.
     */
    public function scopeSiblings($query)
    {
        return$query->where(self::PARENT_ID, $this->parent_model_type_id)
            ->where(self::PRIMARY_KEY, '!=', $this->getKey());
    }
}
