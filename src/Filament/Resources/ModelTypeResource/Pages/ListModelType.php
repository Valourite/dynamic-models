<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\ModelTypeResource;

final class ListModelType extends ListRecords
{
    protected static string $resource = ModelTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
