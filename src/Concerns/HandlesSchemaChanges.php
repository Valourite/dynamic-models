<?php

namespace Valourite\DynamicModels\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Valourite\DynamicModels\Models\ModelType;

trait HandlesSchemaChanges
{
    /**
     * Returns true if the set of custom_ids differs between $old and $new,
     * considering both section custom_ids and field custom_ids within each section.
     */
    protected function checkForChanges(array $old, array $new): bool
    {
        [$oldSections, $oldFieldsBySection] = $this->indexCustomIds($old);
        [$newSections, $newFieldsBySection] = $this->indexCustomIds($new);

        // Different section sets?
        if ($oldSections !== $newSections) {
            return true;
        }

        // Same sections; compare field sets per section
        foreach ($oldFieldsBySection as $sectionCid => $oldFieldSet) {
            // If a section exists in old but not in new 
            // (shouldn't happen if section sets equal)
            if (!array_key_exists($sectionCid, $newFieldsBySection)) {
                return true;
            }

            if ($oldFieldSet !== $newFieldsBySection[$sectionCid]) {
                return true;
            }
        }

        return false;
    }

    protected function schemaChanged(Model $record, array $data)
    {
        //Grab the id of the current record
        // the current record becomes the parent of the new record
        $parentId = $record->getKey();

        $new = new ModelType();
        $new->fill($data);
        $new->{ModelType::PARENT_ID} = $parentId;
        $new->{ModelType::MODEL_TYPE_VERSION} = $this->nextChildVersion(
            $parentId,
            $record->model_type_version,
            config('dynamic-models.increment_count', '0.0.1')
        );

        //save the new record
        $new->save();

        // keep current records values as is, as we have copied the values across
        foreach ($data as $key => $value) {
            if ($key === ModelType::MODEL_TYPE_SCHEMA) {
                continue;
            }

            $data[$key] = $record->$key;
        }

        //set the data model_schema to the records schema 
        // as a new record has been created with that schema
        // and we want to keep the current records schema as is
        $data[ModelType::MODEL_TYPE_SCHEMA] = $record->{ModelType::MODEL_TYPE_SCHEMA};

        return $data;
    }

    protected function schemaRemained(Model $record, array $data)
    {
        //grab the parent id of the current record
        $parentId = $record->{ModelType::PARENT_ID};

        $data[ModelType::MODEL_TYPE_VERSION] = $this->nextChildVersion(
            $parentId,
            $record->model_type_version,
            config('dynamic-models.increment_count', '0.0.1')
        );

        return $data;
    }

    /**
     * Build normalized, order-insensitive indices of custom_ids.
     */
    protected function indexCustomIds(array $schema): array
    {
        $sectionIds = [];
        $fieldsBySection = [];

        foreach ($schema as $sectionKey => $section) {
            if (!is_array($section)) {
                continue;
            }

            // Section custom_id (fallback to array key if missing)
            $sectionCid = $section['custom_id'] ?? (string) $sectionKey;
            $sectionIds[] = $sectionCid;

            // Collect field custom_ids for this section
            $fieldIds = [];
            foreach (($section['Fields'] ?? []) as $fieldKey => $field) {
                if (!is_array($field)) {
                    continue;
                }
                $fieldIds[] = $field['custom_id'] ?? (string) $fieldKey;
            }

            // Normalize field IDs: unique + sorted
            $fieldIds = array_values(array_unique($fieldIds));
            sort($fieldIds, SORT_STRING);

            $fieldsBySection[$sectionCid] = $fieldIds;
        }

        // Normalize section IDs: unique + sorted
        $sectionIds = array_values(array_unique($sectionIds));
        sort($sectionIds, SORT_STRING);

        // Sort the map by section id for deterministic comparison
        ksort($fieldsBySection, SORT_STRING);

        return [$sectionIds, $fieldsBySection];
    }

    protected function nextChildVersion(int|null $parentId, string $currentVersion, string $inc = '0.0.1', bool $resetLowerOnBump = true): string 
    {
        return DB::transaction(function () use ($parentId, $currentVersion, $inc, $resetLowerOnBump) {
            // lock siblings so two writers don’t compute the same base concurrently
            $versions = ModelType::query()
                ->where(ModelType::PARENT_ID, $parentId)
                ->lockForUpdate()
                ->pluck(ModelType::MODEL_TYPE_VERSION)
                ->all();

            $maxSibling = [0, 0, 0];
            foreach ($versions as $v) {
                $t = static::parse((string) $v);
                if (static::compare($t, $maxSibling) === 1) {
                    $maxSibling = $t;
                }
            }

            $base = static::parse($currentVersion);
            if (static::compare($maxSibling, $base) === 1) {
                $base = $maxSibling; // don’t go backwards vs siblings
            }

            $incT = static::parse($inc);
            $next = static::add($base, $incT, $resetLowerOnBump);

            return implode('.', $next);
        });
    }

    protected static function add(array $base, array $inc, bool $resetLowerOnBump): array
    {
        [$M, $m, $p] = $base;
        [$iM, $im, $ip] = $inc;
        if ($resetLowerOnBump && $iM > 0)
            return [$M + $iM, 0, 0];
        if ($resetLowerOnBump && $im > 0)
            return [$M, $m + $im, 0];
        return [$M + $iM, $m + $im, $p + $ip];
    }

    protected static function compare(array $a, array $b): int
    {
        // lexicographic tuple compare
        if ($a[0] !== $b[0])
            return $a[0] <=> $b[0];
        if ($a[1] !== $b[1])
            return $a[1] <=> $b[1];
        return $a[2] <=> $b[2];
    }

    protected static function parse(string $v): array
    {
        // accept "v1.2.3", trim spaces, default to 0.0.0
        $v = trim($v);
        if (preg_match('/(\d+)\.(\d+)\.(\d+)/', $v, $m)) {
            return [intval($m[1]), intval($m[2]), intval($m[3])];
        }
        return [0, 0, 0];
    }

}