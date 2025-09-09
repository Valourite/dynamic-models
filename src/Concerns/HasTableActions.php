<?php

namespace Valourite\DynamicModels\Concerns;

use Filament\Actions;

trait HasTableActions
{
    public static function getTableActions(): array
    {
        $actions = [
            Actions\ViewAction::make(),

            Actions\EditAction::make(),

            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->successNotificationMessage(config('dynamic-models.navigation.label', 'Model Type') . " Deleted"),

            Actions\RestoreAction::make()
                ->requiresConfirmation()
                ->successNotificationMessage(config('dynamic-models.navigation.label', 'Model Type') . " Restored"),
        ];

        if (config('dynamic-models.deleting.allow_force_delete', false)) {
            $actions[] = Actions\ForceDeleteAction::make()
                ->requiresConfirmation()
                ->successNotificationMessage(config('dynamic-models.navigation.label', 'Model Type') . " Permanently Deleted")
                ->label('Delete Permanently');
        }

        return $actions;
    }
}