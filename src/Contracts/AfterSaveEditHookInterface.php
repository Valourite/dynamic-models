<?php

namespace Valourite\DynamicModels\Contracts;

interface AfterSaveEditHookInterface
{
    /**
     * Called after the save completed.
     * New version created determines if a new version was created or not.
     * Return void
     */
    public function afterSave(\Valourite\DynamicModels\Models\ModelType $record, array $data, bool $newVersionCreated): void;
}