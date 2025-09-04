<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\ModelTypeResource;
use Valourite\DynamicModels\Models\ModelType;

final class CreateModelType extends CreateRecord
{
    protected static string $resource = ModelTypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {        
        //Set the schema data to the schema data provided by $this->data as it contains
        //the missing action data that gets removed from $form->getState()
        $data[ModelType::MODEL_TYPE_SCHEMA] = $this->data[ModelType::MODEL_TYPE_SCHEMA];

        //return new data
        return $data;
    }
}
