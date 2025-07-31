<?php

namespace Valourite\DynamicModels\Models;

use Illuminate\Database\Eloquent\Model;
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

    public const MODEL_INSTANCE_DATA = 'model_instance_data';

    public const PRIMARY_KEY = 'model_instance_id';

    public const MORPH_NAME = 'parent_model';

    public const BASE_TABLE_NAME = 'model_instances';

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
        self::MODEL_TYPE_ID       => 'int',
        self::PARENT_MODEL_ID     => 'int',
        self::MODEL_INSTANCE_DATA => 'json',
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
        self::MODEL_INSTANCE_DATA,
    ];

    /**
     * =========================
     * 		 WITH
     * ========================.
     */
    protected $with = ['parentModel', 'modelType'];

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
    public function parentModel()
    {
        return $this->morphTo();
    }
}
