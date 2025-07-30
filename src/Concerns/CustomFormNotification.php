<?php

namespace Valourite\FormBuilder\Concerns;

use Filament\Notifications\Notification;

/**
 * This trait will be used to display a custom notification on create.
 */
trait CustomFormNotification
{
    /**
     * Allows a custom notification to be used when the model is created and form filled.
     *
     * @return Notification
     */
    protected function getCreatedNotification(): ?Notification
    {
        $confirmationMessage = $this->record->form->form_confirmation_message ?? null;

        return Notification::make()
            ->success()
            ->title($confirmationMessage ?? 'Form submitted successfully!');
    }

    /**
     * Allows a custom notification to be used when the model is saved and form filled.
     *
     * @return Notification
     */
    protected function getSavedNotification(): ?Notification
    {
        $confirmationMessage = $this->record->form->form_confirmation_message ?? null;

        return Notification::make()
            ->success()
            ->title($confirmationMessage ?? 'Form submitted successfully!');
    }
}
