<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Valourite\DynamicModels\Concerns\HandlesSchemaChanges;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\ModelTypeResource;
use Valourite\DynamicModels\Models\ModelType;
use Valourite\DynamicModels\Support\Semver;

final class EditModelType extends EditRecord
{
    use HandlesSchemaChanges;

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
        //Set the schema data to the schema data provided by $this->data as it contains
        //the missing action data that gets removed from $form->getState()
        $data[ModelType::MODEL_TYPE_SCHEMA] = $this->data[ModelType::MODEL_TYPE_SCHEMA];

        $record = $this->getRecord();

        $diff = static::checkForChanges(
            $record->model_type_schema,
            $data['model_type_schema']
        );

        $data = $diff ? static::schemaChanged($record, $data) : static::schemaRemained($record, $data);

        return $data;
    }
}
