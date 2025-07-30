<?php

namespace Valourite\FormBuilder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Valourite\FormBuilder\Database\Factories\FormFactory;

final class Form extends Model
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
    public const FORM_ID = 'form_id';

    public const FROM_NAME = 'form_name';

    public const FORM_SLUG = 'form_slug';

    public const FORM_DESCRIPTION = 'form_description';

    public const FORM_CONFIRMATION_MESSAGE = 'form_confirmation_message';

    public const IS_ACTIVE = 'is_active';

    public const FORM_MODEL = 'form_model';

    public const FORM_CONTENT = 'form_content';

    public const FORM_VERSION = 'form_version';

    public const PRIMARY_KEY = 'form_id';

    public const BASE_TABLE_NAME = 'forms';

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
        self::IS_ACTIVE    => 'boolean',
        self::FORM_CONTENT => 'json',
    ];

    /**
     * =========================
     *		 FILLABLE
     * =========================.
     */
    protected $fillable = [
        self::FROM_NAME,
        self::FORM_SLUG,
        self::FORM_DESCRIPTION,
        self::FORM_CONFIRMATION_MESSAGE,
        self::IS_ACTIVE,
        self::FORM_MODEL,
        self::FORM_CONTENT,
        self::FORM_VERSION,
    ];

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

    /**
     * =======================
     *      BOOTED
     * =======================.
     */
    public static function booted(): void
    {
        // Allow the slug to be generated from the form
        static::creating(function ($model) {
            $model->form_slug = str($model->form_name)->slug();

            if ($model->form_content === null) {
                $model->form_content = json_encode('{}');
            }
        });
    }

    /*
     * =========================
     *		 FACTORY
     * =========================
     */

    public static function factory(): FormFactory
    {
        return FormFactory::new();
    }

    /*
     * =========================
     *		 RELATIONS
     * =========================
     */

    public function responses()
    {
        return $this->hasMany(FormResponse::class, self::PRIMARY_KEY);
    }
}
