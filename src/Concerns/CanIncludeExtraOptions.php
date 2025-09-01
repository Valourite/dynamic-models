<?php

namespace Valourite\DynamicModels\Concerns;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Valourite\DynamicModels\Filament\Enums\FieldType;

/**
 * This trait will be used to include extra options into the field or section options.
 */
trait CanIncludeExtraOptions
{
    use CanIncludeFieldTypes;

    public static function getFieldExtraOptions(null|FieldType|string $field = null): ?Component
    {
        if ( ! $field) {
            return null;
        }

        $fieldType = is_string($field) ? FieldType::tryFrom(mb_strtoupper($field)) : $field;

        if ( ! $fieldType) {
            return null;
        }

        return static::getFieldTypeOptionsSection($fieldType->value);
    }
}
