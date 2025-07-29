<?php

namespace Valourite\FormBuilder\Concerns;

use Filament\Notifications\Notification;

/**
 * This trait will be used to display a custom notification on create
 */
trait CustomFormNotification
{
    /**
     * Allows a custom notification to be used when the model is created and form filled
     * @return Notification
     */
    protected function getCreatedNotification(): ?Notification
    {
        $form = $this->record->form ?? null;

        return Notification::make()
            ->success()
            ->title($form ? $form->form_confirmation_message : 'Form submitted successfully!');
    }

    /**
     * Allows a custom notification to be used when the model is saved and form filled
     * @return Notification
     */
    protected function getSavedNotification(): ?Notification
    {
        $form = $this->record->form ?? null;

        return Notification::make()
            ->success()
            ->title($form ? $form->form_confirmation_message : 'Form submitted successfully!');
    }
}