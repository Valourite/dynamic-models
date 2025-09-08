<?php

namespace Valourite\DynamicModels\Contracts;

use Valourite\DynamicModels\Models\ModelType;

interface StrategyInterface
{
    /**
     * Return the next version string given the current record and inbound $data.
     * Return null to let the default algorithm run.
     */
    public function computeNextVersion(ModelType $record, array $data): ?string;

    /**
     * Called when schema changed and a NEW version was created.
     * Implementors can e.g. disable previous version, copy flags, etc.
     */
    public function onSchemaChanged(ModelType $record, array $data): array;

    /**
     * Called when schema remained (no new version).
     */
    public function onSchemaRemained(ModelType $record, array $data): array;

    /**
     * Returns true if the set of custom_ids differs between $old and $new,
     * considering both section custom_ids and field custom_ids within each section.
     */
    public function hasSchemaChanged(array $originalSchema, array $newSchema): bool;
}
