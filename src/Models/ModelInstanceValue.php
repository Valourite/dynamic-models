<?php

namespace Valourite\DynamicModels\Models;

use Carbon\Carbon;
use DateTime;
use Exception;
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

    /**
     * Get the value attribute with automatic handling for different field types.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        // If no value or no type, return as is
        if ($value === null || $this->type === null) {
            return $value;
        }

        // Handle different field types
        if (is_string($value)) {
            // Handle select fields with JSON values (multiple select)
            if ($this->type === FieldType::SELECT) {
                // First, try to see if this is a JSON array (multiple select)
                if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
                    try {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            logger('Successfully decoded JSON array for select field: ' . json_encode($decoded));

                            return $decoded;
                        }
                        logger("Failed to decode JSON array for select field: {$value}, JSON error: " . json_last_error_msg());
                    } catch (Exception $e) {
                        // If decoding fails, return original value
                        logger('Exception when decoding JSON for select field: ' . $e->getMessage());
                    }
                }

                // If it's not a JSON array, check if we need to convert it to an array based on field configuration
                // This helps handle cases where a single value needs to be treated as an array
                $field      = $this->getFieldDefinition();
                $isMultiple = false;

                // Check for multiple in different possible locations in the field structure
                if ( ! empty($field)) {
                    if (isset($field['extra']['multiple']) && $field['extra']['multiple']) {
                        $isMultiple = true;
                        logger("Field is multiple select based on field['extra']['multiple']");
                    } elseif (isset($field['multiple']) && $field['multiple']) {
                        $isMultiple = true;
                        logger("Field is multiple select based on field['multiple']");
                    }
                }

                if ($isMultiple && ! is_array($value)) {
                    // For a multiple select field with a single value, convert to array
                    logger("Converting single value to array for multiple select field: {$value}");

                    return [$value];
                }
            }

            // Handle date fields - convert to Carbon instance
            if ($this->type === FieldType::DATE && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                try {
                    return Carbon::createFromFormat('Y-m-d', $value);
                } catch (Exception $e) {
                    // If parsing fails, return original value
                }
            }

            // Handle datetime fields - convert to Carbon instance
            if ($this->type === FieldType::DATETIME && str_contains($value, ' ')) {
                try {
                    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                        return Carbon::createFromFormat('Y-m-d H:i:s', $value);
                    }

                    return Carbon::parse($value);
                } catch (Exception $e) {
                    // If parsing fails, return original value
                }
            }

            // Handle time fields - convert to Carbon instance
            if ($this->type === FieldType::TIME && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                try {
                    $format = mb_strlen($value) === 8 ? 'H:i:s' : 'H:i';

                    return Carbon::createFromFormat($format, $value);
                } catch (Exception $e) {
                    // If parsing fails, return original value
                }
            }
        }

        return $value;
    }

    public function setValueAttribute($value)
    {
        // If value is null, just store it
        if ($value === null) {
            $this->attributes[self::VALUE] = null;

            return;
        }

        // Get the field type if available
        $fieldType = $this->attributes[self::TYPE] ?? null;

        // Handle different value types based on field type
        if ($fieldType === FieldType::SELECT->value) {
            // Check if this is a multiple select field
            $field      = $this->getFieldDefinition();
            $isMultiple = false;

            // Check for multiple in different possible locations in the field structure
            if ( ! empty($field)) {
                if (isset($field['extra']['multiple']) && $field['extra']['multiple']) {
                    $isMultiple = true;
                    logger("Field is multiple select based on field['extra']['multiple'] in setValueAttribute");
                } elseif (isset($field['multiple']) && $field['multiple']) {
                    $isMultiple = true;
                    logger("Field is multiple select based on field['multiple'] in setValueAttribute");
                }
            }

            // Handle arrays (for multiple select)
            if (is_array($value)) {
                logger('Converting array to JSON for SELECT field: ' . json_encode($value));
                $this->attributes[self::VALUE] = json_encode($value);
            }
            // If it's a string that looks like a JSON array
            elseif (is_string($value) && str_starts_with($value, '[') && str_ends_with($value, ']')) {
                // Validate it's a proper JSON array
                try {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        // It's already a valid JSON string, store as is
                        logger("Storing pre-encoded JSON array for SELECT field: {$value}");
                        $this->attributes[self::VALUE] = $value;
                    } else {
                        // Invalid JSON, store as regular string
                        logger("Invalid JSON, storing as regular string: {$value}");
                        $this->attributes[self::VALUE] = $value;
                    }
                } catch (Exception $e) {
                    // Exception during JSON parsing, store as regular string
                    logger("JSON parsing exception, storing as regular string: {$value}");
                    $this->attributes[self::VALUE] = $value;
                }
            }
            // For multiple select fields with a single scalar value, convert to JSON array
            elseif ($isMultiple) {
                logger("Converting single value to JSON array for multiple SELECT field: {$value}");
                $this->attributes[self::VALUE] = json_encode([$value]);
            }
            // Regular string for single select
            else {
                logger("Storing regular string for single SELECT field: {$value}");
                $this->attributes[self::VALUE] = $value;
            }
        }
        // Carbon/DateTime instances
        elseif (($value instanceof Carbon || $value instanceof DateTime)) {
            if ($fieldType === FieldType::DATE->value) {
                $this->attributes[self::VALUE] = $value->format('Y-m-d');
            } elseif ($fieldType === FieldType::DATETIME->value) {
                $this->attributes[self::VALUE] = $value->format('Y-m-d H:i:s');
            } elseif ($fieldType === FieldType::TIME->value) {
                $this->attributes[self::VALUE] = $value->format('H:i:s');
            } else {
                // For other field types, just use the string value
                $this->attributes[self::VALUE] = (string) $value;
            }
        }
        // String values that might be dates
        elseif (is_string($value) && in_array($fieldType, [
            FieldType::DATE->value,
            FieldType::DATETIME->value,
            FieldType::TIME->value,
        ])) {
            try {
                $date = Carbon::parse($value);

                if ($fieldType === FieldType::DATE->value) {
                    $this->attributes[self::VALUE] = $date->format('Y-m-d');
                } elseif ($fieldType === FieldType::DATETIME->value) {
                    $this->attributes[self::VALUE] = $date->format('Y-m-d H:i:s');
                } elseif ($fieldType === FieldType::TIME->value) {
                    $this->attributes[self::VALUE] = $date->format('H:i:s');
                }
            } catch (Exception $e) {
                // If parsing fails, store the original string
                $this->attributes[self::VALUE] = $value;
            }
        }
        // All other values
        else {
            $this->attributes[self::VALUE] = $value;
        }
    }

    /**
     * Get the field definition for this value from the model type.
     *
     * @return array|null
     */
    protected function getFieldDefinition(): ?array
    {
        if ( ! $this->model_instance_id || ! $this->field_id) {
            return null;
        }

        try {
            // Get the model instance
            $modelInstance = ModelInstance::find($this->model_instance_id);
            if ( ! $modelInstance || ! $modelInstance->model_type_id) {
                return null;
            }

            // Get the model type
            $modelType = ModelType::find($modelInstance->model_type_id);
            if ( ! $modelType) {
                return null;
            }

            // Get the schema and find the field definition
            $schema = $modelType->schema;
            if ( ! $schema || ! isset($schema['fields'])) {
                return null;
            }

            // Find the field in the schema
            foreach ($schema['fields'] as $field) {
                if (isset($field['id']) && $field['id'] == $this->field_id) {
                    return $field;
                }
            }
        } catch (Exception $e) {
            logger('Error getting field definition: ' . $e->getMessage());
        }

        return null;
    }
}
