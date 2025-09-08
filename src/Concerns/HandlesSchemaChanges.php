<?php

namespace Valourite\DynamicModels\Concerns;

use Illuminate\Support\Facades\DB;
use Valourite\DynamicModels\Models\ModelType;

trait HandlesSchemaChanges
{
    protected static function add(array $base, array $inc, bool $resetLowerOnBump): array
    {
        [$bMaj, $bMin, $bPat] = array_map('intval', $base);
        [$iMaj, $iMin, $iPat] = array_map('intval', $inc);

        // --- PATCH ---
        $sumPat   = $bPat + $iPat;
        $carryMin = intdiv($sumPat, 10);

        // If resetting, *any* change to a higher unit (minor/major) should zero patch:
        // - explicit minor/major increments
        // - or a carry up from patch
        $patch = $resetLowerOnBump && ($iMin > 0 || $iMaj > 0 || $carryMin > 0)
            ? 0
            : ($sumPat % 10);

        // --- MINOR ---
        $sumMin   = $bMin + $iMin + $carryMin;
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
        if ($current[0] !== $base[0]) {
            return $current[0] <=> $base[0];
        }
        if ($current[1] !== $base[1]) {
            return $current[1] <=> $base[1];
        }

        return $current[2] <=> $base[2];
    }

    /**
     * accept "v1.2.3", trim spaces, default to 0.0.0.
     *
     * @param string $version
     *
     * @return int[]
     */
    protected static function parse(string $version): array
    {
        //strip whitespaces
        $version = trim($version);

        //grab numeric values
        if (preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $m)) {
            return [(int) ($m[1]), (int) ($m[2]), (int) ($m[3])];
        }

        //default
        return [0, 0, 0];
    }

    /**
     * Build normalized, order-insensitive indices of custom_ids.
     */
    protected function indexCustomIds(array $schema): array
    {
        $sectionIDs      = [];
        $fieldsBySection = [];

        foreach ($schema as $section) {
            if ( ! is_array($section)) {
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
                if ( ! is_array($field)) {
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
            $next             = static::add($base, $valueToIncrement, $resetLowerOnBump);

            return implode('.', $next);
        });
    }
}
