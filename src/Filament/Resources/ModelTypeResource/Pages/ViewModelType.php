<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\ModelTypeResource;

final class ViewModelType extends ViewRecord
{
    protected static string $resource = ModelTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
