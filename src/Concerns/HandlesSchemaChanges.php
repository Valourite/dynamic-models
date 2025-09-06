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

        // keep current records values as is, 
        // as we have copied the values across
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

    /**
     * Update version if user updated version
     * Else just return data, do not update version
     */
    protected function schemaRemained(Model $record, array $data)
    {
        //grab the parent id of the current record
        $parentId = $record->{ModelType::PARENT_ID};

        //test if the user changed the version manually
        // return data with no updated version from nextChildVersion
        if ($data[ModelType::MODEL_TYPE_VERSION] !== $record->model_type_version) {
            return $data;
        }

        //To consider: Should we update the version if the user did not change the schema?
        // $data[ModelType::MODEL_TYPE_VERSION] = $this->nextChildVersion(
        //     $parentId,
        //     $record->model_type_version,
        //     config('dynamic-models.increment_count', '0.0.1')
        // );

        return $data;
    }

    /**
     * Build normalized, order-insensitive indices of custom_ids.
     */
    protected function indexCustomIds(array $schema): array
    {
        $sectionIDs = [];
        $fieldsBySection = [];

        foreach ($schema as $section) {
            if (!is_array($section)) {
                continue;
            }

            // Section custom_id
            $sectionCustomID = (string) ($section['custom_id'] ?? '');
            if ($sectionCustomID === '') {
                continue;
            }

            $sectionIDs[] = $sectionCustomID;

            // Collect field custom_ids for this section
            $fieldIDs = [];
            foreach (($section['Fields'] ?? []) as $field) {
                if (!is_array($field)) {
                    continue;
                }

                $fieldCustomID = (string) ($field['custom_id'] ?? '');
                if ($fieldCustomID === '') {
                    continue;
                }

                // Field custom_id
                $fieldIDs[] = $field['custom_id'];
            }

            // Normalize field IDs: unique + sorted
            $fieldIDs = array_values(array_unique($fieldIDs));
            sort($fieldIDs, SORT_STRING);

            $fieldsBySection[$sectionCustomID] = $fieldIDs;
        }

        // Normalize section IDs: unique + sorted
        $sectionIDs = array_values(array_unique($sectionIDs));
        sort($sectionIDs, SORT_STRING);

        // Sort the map by section id for deterministic comparison
        ksort($fieldsBySection, SORT_STRING);

        return [$sectionIDs, $fieldsBySection];
    }

    protected function nextChildVersion(int|null $parentId, string $currentVersion, string $incrementValue = '0.0.1', bool $resetLowerOnBump = true): string
    {
        return DB::transaction(function () use ($parentId, $currentVersion, $incrementValue, $resetLowerOnBump) {
            // lock siblings so two writers don’t compute the same base concurrently
            $versions = ModelType::query()
                ->where(ModelType::PARENT_ID, $parentId)
                ->lockForUpdate()
                ->pluck(ModelType::MODEL_TYPE_VERSION)
                ->all();

            $maxVersion = [0, 0, 0];
            foreach ($versions as $version) {
                $parsed = static::parse((string) $version);
                if (static::compare($parsed, $maxVersion) === 1) {
                    $maxVersion = $parsed;
                }
            }

            $base = static::parse($currentVersion);
            if (static::compare($maxVersion, $base) === 1) {
                $base = $maxVersion; // don’t go backwards vs siblings
            }

            $valueToIncrement = static::parse($incrementValue);
            $next = static::add($base, $valueToIncrement, $resetLowerOnBump);

            return implode('.', $next);
        });
    }

    protected static function add(array $base, array $inc, bool $resetLowerOnBump): array
    {
        [$bMaj, $bMin, $bPat] = array_map('intval', $base);
        [$iMaj, $iMin, $iPat] = array_map('intval', $inc);

        // --- PATCH ---
        $sumPat = $bPat + $iPat;
        $carryMin = intdiv($sumPat, 10);

        // If resetting, *any* change to a higher unit (minor/major) should zero patch:
        // - explicit minor/major increments
        // - or a carry up from patch
        $patch = $resetLowerOnBump && ($iMin > 0 || $iMaj > 0 || $carryMin > 0)
            ? 0
            : ($sumPat % 10);

        // --- MINOR ---
        $sumMin = $bMin + $iMin + $carryMin;
        $carryMaj = intdiv($sumMin, 10);

        // If resetting, any change to major (explicit or via carry) should zero minor,
        // and explicit minor increment should also zero patch (already handled) and set minor to summed (mod 10) unless carry/bump happens.
        $minor = $resetLowerOnBump && ($iMaj > 0 || $carryMaj > 0)
            ? 0
            : ($sumMin % 10);

        if ($resetLowerOnBump && ($iMaj > 0 || $carryMaj > 0)) {
            // ensure both lower parts are zeroed on a major bump
            $minor = 0;
            $patch = 0;
        }

        // --- MAJOR ---
        $major = $bMaj + $iMaj + $carryMaj;

        return [$major, $minor, $patch];
    }

    protected static function compare(array $current, array $base): int
    {
        // lexicographic tuple compare
        if ($current[0] !== $base[0])
            return $current[0] <=> $base[0];
        if ($current[1] !== $base[1])
            return $current[1] <=> $base[1];
        return $current[2] <=> $base[2];
    }

    /**
     * accept "v1.2.3", trim spaces, default to 0.0.0
     * @param string $version
     * @return int[]
     */
    protected static function parse(string $version): array
    {
        //strip whitespaces
        $version = trim($version);

        //grab numeric values
        if (preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $m)) {
            return [intval($m[1]), intval($m[2]), intval($m[3])];
        }

        //default
        return [0, 0, 0];
    }

}