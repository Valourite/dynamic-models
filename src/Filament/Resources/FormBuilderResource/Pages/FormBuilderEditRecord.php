<?php

namespace Valourite\FormBuilder\Filament\Resources\FormBuilderResource\Pages;

use Filament\Resources\Pages\EditRecord;

abstract class FormBuilderEditRecord extends EditRecord
{
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $model    = static::getModel();
        $instance = new $model();

        // clear the form response
        unset($data[$instance->getFormResponseColumn()]);

        $formResponse = [];

        $formData = is_array($data[$instance->getFormContentColumn()])
            ? $data[$instance->getFormContentColumn()]
            : json_decode($data[$instance->getFormContentColumn()] ?? [], true);

        foreach ($formData as $section) {
            foreach ($section['Fields'] ?? [] as $field) {
                if (isset($field['custom_id'], $data[$field['custom_id']])) {
                    $formResponse[$field['custom_id']] = $data[$field['custom_id']];
                    unset($data[$field['custom_id']]);
                }
            }
        }

        $data[$instance->getFormResponseColumn()] = json_encode($formResponse);
        $data[$instance->getFormContentColumn()]  = json_encode($formData);
        $data['form_schema'] = $formData;

        return $data;
    }
}
