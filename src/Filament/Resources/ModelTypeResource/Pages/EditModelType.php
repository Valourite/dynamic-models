<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\ModelTypeResource;
use Valourite\DynamicModels\Models\ModelType;

final class EditModelType extends EditRecord
{
    protected static string $resource = ModelTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        $recordForm = $record->model_type_schema;
        $dataForm   = $data['model_type_schema'];

        $diff = static::hasModelTypeSchemaChanged($recordForm, $dataForm);

        //We need to compare to arrays to see if they're identical
        if ($diff) {
            $newModelType = new ModelType();

            $newModelType->fill($data);

            $newModelType->model_type_version = $this->incrementVersion(
                $record->model_type_version,
                config('dynamic-models.increment_count', '0.0.1')
            );

            $newModelType->save();

            // new model type has been created, revert the data back to original
            $data['model_type_schema'] = $record->model_type_schema;

            // //redirect to the new form view page
            // return redirect(FormResource::getUrl('edit', ['record' => $newModelType]));
        } else {
            // increment form version
            $data['model_type_version'] = $this->incrementVersion(
                $record->model_type_version,
                config('dynamic-models.increment_count', '0.0.1')
            );
        }

        return $data;
    }

    protected function incrementVersion(string $currentVersion, string $increment = '0.0.1'): string
    {
        [$major, $minor, $patch]          = array_map('intval', explode('.', $currentVersion));
        [$incMajor, $incMinor, $incPatch] = array_map('intval', explode('.', $increment));

        $newPatch = $patch + $incPatch;
        $newMinor = $minor + $incMinor;
        $newMajor = $major + $incMajor;

        return "{$newMajor}.{$newMinor}.{$newPatch}";
    }

    protected function hasModelTypeSchemaChanged(array $old, array $new): bool
    {
        $normalize = fn (array $content) => collect($content)
            ->map(function ($section) {
                // Strip metadata
                unset($section['title'], $section['prefix_icon']);

                // Normalize fields
                $section['Fields'] = collect($section['Fields'] ?? [])
                    ->map(function ($field) {
                        unset($field['label'], $field['prefix_icon']);

                        return $field;
                    })
                    // Sort fields by custom_id for consistent structure
                    ->sortBy('custom_id')
                    ->values()
                    ->toArray();

                return $section;
            })
            // Sort sections by custom_id for consistent comparison
            ->sortBy('custom_id')
            ->values()
            ->toArray();

        return md5(json_encode($normalize($old))) !== md5(json_encode($normalize($new)));
    }
}
