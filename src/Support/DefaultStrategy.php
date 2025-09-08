<?php

namespace Valourite\DynamicModels\Support;

use Illuminate\Database\Eloquent\Model;
use Valourite\DynamicModels\Concerns\HandlesSchemaChanges;
use Valourite\DynamicModels\Contracts\StrategyInterface;
use Valourite\DynamicModels\Models\ModelType;

class DefaultStrategy implements StrategyInterface
{
    use HandlesSchemaChanges;

    public function computeNextVersion(ModelType $record, array $data): ?string
    {
        // use default algorithm
        return $this->nextChildVersion(
            $record->{ModelType::PARENT_ID},
            $record->model_type_version,
            config('dynamic-models.versioning.increment_count', '0.0.1')
        );
    }

    public function hasSchemaChanged(array $originalSchema, array $newSchema): bool
    {
        [$oldSections, $oldFieldsBySection] = $this->indexCustomIds($originalSchema);
        [$newSections, $newFieldsBySection] = $this->indexCustomIds($newSchema);

        // Different section sets?
        if ($oldSections !== $newSections) {
            return true;
        }

        // Same sections; compare field sets per section
        foreach ($oldFieldsBySection as $sectionCustomID => $oldFieldSet) {
            // If a section exists in old but not in new 
            // (shouldn'currentVersion happen if section sets equal)
            if (!array_key_exists($sectionCustomID, $newFieldsBySection)) {
                return true;
            }

            if ($oldFieldSet !== $newFieldsBySection[$sectionCustomID]) {
                return true;
            }
        }

        return false;
    }

    public function onSchemaChanged(ModelType $record, array $data): array
    {
        //Grab the id of the current record
        // the current record becomes the parent of the new record
        $parentId = $record->getKey();

        if (config('dynamic-models.versioning.create_new', true)) {
            $new = new ModelType();
            $new->fill($data);
            $new->{ModelType::PARENT_ID} = $parentId;
            $new->{ModelType::MODEL_TYPE_VERSION} = $this->computeNextVersion($record, $data);

            //save the new record
            $new->save();

            // test if previous version should be disabled
            if (config('dynamic-models.versioning.disable_previous_on_new', false)) {
                $record->forceFill([ModelType::CAN_BE_CREATED => false])->saveQuietly();
            }
        } else {
            $data[ModelType::MODEL_TYPE_VERSION] = $this->computeNextVersion($record, $data);
        }

        // keep current records values as is, 
        // as we have copied the values across
        foreach ($data as $key => $value) {
            if ($key === ModelType::MODEL_TYPE_VERSION) {
                continue;
            }

            $data[$key] = $record->$key;
        }

        //set the data model_schema to the records schema 
        // as a new record has been created with that schema
        // and we want to keep the current records schema as is
        // $data[ModelType::MODEL_TYPE_SCHEMA] = $record->{ModelType::MODEL_TYPE_SCHEMA};

        return $data;
    }

    public function onSchemaRemained(ModelType $record, array $data): array
    {
        //test if the user changed the version manually
        // return data with no updated version from nextChildVersion
        if ($data[ModelType::MODEL_TYPE_VERSION] !== $record->model_type_version) {
            return $data;
        }

        if (!config('dynamic-models.versioning.update_only_on_schema_change', false)) {
            $data[ModelType::MODEL_TYPE_VERSION] = $this->computeNextVersion($record, $data);
        }

        return $data;
    }
}