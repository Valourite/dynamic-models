<?php

namespace Valourite\DynamicModels\Contracts;

interface EditHook
{
    /**
     * Mutate incoming form data before the package decides about versioning.
     * Return $data
     */
    public function beforeSave(ModelType $record, array $data): array;

    /**
     * Called after the save completed. $newVersionCreated tells which path ran.
     */
    public function afterSave(ModelType $record, array $data, bool $newVersionCreated): void;
}