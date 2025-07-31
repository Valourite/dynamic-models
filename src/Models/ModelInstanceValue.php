<?php

namespace Valourite\DynamicModels\Models;

use Illuminate\Database\Eloquent\Model;
use Valourite\DynamicModels\Concerns\BelongsToModelInstance;
use Valourite\DynamicModels\Filament\Enums\FieldType;

final class ModelInstanceValue extends Model
{
    /**
     * =========================
     *		 TRAIT
     * =========================.
     */
    use BelongsToModelInstance;

    /**
     * ==========================
     *		 CONSTANTS
     * ==========================.
     */
    public const MODEL_INSTANCE_VALUE_ID = 'model_instance_value_id';

    public const MODEL_INSTANCE_ID = 'model_instance_id';

    public const NAME = 'name';

    public const FIELD_ID = 'field_id';

    public const VALUE = 'value';

    public const TYPE = 'type';

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = 'updated_at';

    public const PRIMARY_KEY = 'model_instance_value_id';

    public const BASE_TABLE_NAME = 'model_instance_values';

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
        self::MODEL_INSTANCE_VALUE_ID => 'int',
        self::MODEL_INSTANCE_ID       => 'int',
        self::TYPE                    => FieldType::class,
    ];

    /**
     * =========================
     *		 FILLABLE
     * =========================.
     */
    protected $fillable = [
        self::MODEL_INSTANCE_ID,
        self::NAME,
        self::FIELD_ID,
        self::VALUE,
        self::TYPE,
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
}
