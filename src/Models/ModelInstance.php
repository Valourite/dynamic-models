<?php

namespace Valourite\DynamicModels\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Valourite\DynamicModels\Concerns\BelongsToType;

final class ModelInstance extends Model
{
    /**
     * =========================
     *		 TRAIT
     * =========================.
     */
    use BelongsToType;

    /**
     * ==========================
     *		 CONSTANTS
     * ==========================.
     */
    public const MODEL_INSTANCE_ID = 'model_instance_id';

    public const MODEL_TYPE_ID = 'model_type_id';

    public const PARENT_MODEL_ID = 'parent_model_id';

    public const PARENT_MODEL_TYPE = 'parent_model_type';

    public const PRIMARY_KEY = 'model_instance_id';

    public const MORPH_NAME = 'parent_model';

    public const BASE_TABLE_NAME = 'model_instances';

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
        self::MODEL_TYPE_ID   => 'int',
        self::PARENT_MODEL_ID => 'int',
    ];

    /**
     * =========================
     *		 FILLABLE
     * =========================.
     */
    protected $fillable = [
        self::MODEL_TYPE_ID,
        self::PARENT_MODEL_ID,
        self::PARENT_MODEL_TYPE,
    ];

    /**
     * =========================
     * 		 WITH
     * ========================.
     */
    //NOTE: We do not grab the parent model as we only access this model from the parent model
    //We want to grab the model type and the values set
    protected $with = ['modelType', 'modelInstanceValues'];

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
        //Delete all the values when this model is deleted
        static::deleting(function ($model) {
            $model->modelInstanceValues()->delete();
        });
    }

    /*
     * =========================
     *		 RELATIONS
     * =========================
     */
    /**
     * Returns the model this response belongs to.
     *
     * @return MorphTo
     */
    public function parentModel()
    {
        return $this->morphTo();
    }

    /**
     * Returns the model instance values this model instance has.
     *
     * @return HasMany
     */
    public function modelInstanceValues()
    {
        return $this->hasMany(ModelInstanceValue::class, self::PRIMARY_KEY);
    }
}
