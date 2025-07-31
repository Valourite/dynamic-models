<?php

namespace Valourite\DynamicModels\Concerns;

use Filament\Notifications\Notification;

/**
 * This trait will be used to display a custom notification on create.
 */
trait CustomNotification
{
    /**
     * Allows a custom notification to be used when the model is created and form filled.
     *
     * @return Notification
     */
    protected function getCreatedNotification(): ?Notification
    {
        $confirmationMessage = $this->record->modelType->model_type_confirmation_message ?? null;

        return Notification::make()
            ->success()
            ->title($confirmationMessage ?? 'Record created successfully!');
    }

    /**
     * Allows a custom notification to be used when the model is saved and form filled.
     *
     * @return Notification
     */
    protected function getSavedNotification(): ?Notification
    {
        $confirmationMessage = $this->record->modelType->model_type_confirmation_message ?? null;

        return Notification::make()
            ->success()
            ->title($confirmationMessage ?? 'Record saved successfully!');
    }
}
