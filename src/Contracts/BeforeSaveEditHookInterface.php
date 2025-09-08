<?php

namespace Valourite\DynamicModels\Contracts;

interface BeforeSaveEditHookInterface
{
    /**
     * Mutate incoming form data before the package decides about versioning.
     * Return $data
     */
    public function beforeSave(\Valourite\DynamicModels\Models\ModelType $record, array $data): array;
}