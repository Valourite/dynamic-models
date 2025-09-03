<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\ModelTypeResource;

final class CreateModelType extends CreateRecord
{
    protected static string $resource = ModelTypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Process data if needed
        dd($data);
        return $data;
    }
}
