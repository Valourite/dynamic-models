<?php

namespace Valourite\DynamicModels\Concerns;

use Filament\Forms\Components\Hidden;

/**
 * This trait wlll be used to inject hidden fields into the field or section options
 */
trait CanIncludeHiddenFields
{
    public static function getHiddenField(string $name)
    {
        return Hidden::make($name);
    }
}