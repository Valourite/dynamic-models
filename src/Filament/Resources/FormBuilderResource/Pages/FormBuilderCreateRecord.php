<?php

namespace Valourite\FormBuilder\Filament\Resources\FormBuilderResource\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Valourite\FormBuilder\Models\Form;

abstract class FormBuilderCreateRecord extends CreateRecord
{
    protected function getCreatedNotification(): ?Notification
    {
        $form = $this->record->form ?? Form::find($this->formID);

        return Notification::make()
            ->success()
            ->title($form->form_confirmation_message ?? 'Form submitted successfully!');
    }

    protected function handleRecordCreation(array $data): Model
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $this->getModel();

        /** @var Model $record */
        $record = new $modelClass();

        $formData     = $data[$modelClass::getFormContentColumn()] ?? [];
        $formResponse = [];

        foreach ($formData as $section) {
            foreach ($section['Fields'] ?? [] as $field) {
                if (isset($field['custom_id'], $data[$field['custom_id']])) {
                    $formResponse[$field['custom_id']] = $data[$field['custom_id']];
                    unset($data[$field['custom_id']]);
                }
            }
        }

        //set the form data
        $record->{$modelClass::getFormContentColumn()}  = json_encode($formData);
        $record->{$modelClass::getFormResponseColumn()} = json_encode($formResponse);
        $record->{$modelClass::getFormIdColumn()}       = $data['form_id'] ?? null;
        $record->{$modelClass::getFormVersionColumn()}  = $data['form_version'] ?? null;
        $record->form_schema = $formData;

        //unset the form data
        unset($data[$modelClass::getFormContentColumn()], $data[$modelClass::getFormResponseColumn()], $data[$modelClass::getFormIdColumn()], $data[$modelClass::getFormVersionColumn()]);

        // Set all other data manually to avoid fillable issues
        foreach ($data as $key => $value) {
            $record->{$key} = $value;
        }

        // Attach parent if needed
        if ($parent = $this->getParentRecord()) {
            return $this->associateRecordWithParent($record, $parent);
        }

        $record->save();

        return $record;
    }
}
