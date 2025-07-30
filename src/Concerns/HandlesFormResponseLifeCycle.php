<?php

namespace Valourite\FormBuilder\Concerns;

use Illuminate\Database\Eloquent\Model;
use Valourite\FormBuilder\Models\Form;
use Valourite\FormBuilder\Models\FormResponse;

trait HandlesFormResponseLifeCycle
{
    public function afterValidate()
    {
        $this->formBuilderRawData = $this->data;

        // Reject form-builder fields
        $this->data = collect($this->data)
            ->reject(
                fn ($_, $key) => $key === 'form_id' ||
                str_starts_with($key, 'field-')
            )
            ->all();
    }

    protected function afterSave(): void
    {
        $this->createOrUpdateFormResponse();

        //fill the form after save to remount the data
        $this->fillForm();
    }

    protected function afterCreate(): void
    {
        $this->createOrUpdateFormResponse();
    }

    protected function createOrUpdateFormResponse(): void
    {
        if ( ! method_exists($this->record, 'response') || ! method_exists($this->record, 'form')) {
            return;
        }

        $formId = $this->formBuilderRawData['form_id'] ?? null;
        if ( ! $formId) {
            return;
        }

        $form = Form::find($formId);
        if ( ! $form) {
            return;
        }

        $formContent  = $form->form_content ?? [];
        $responseData = [];

        foreach ($formContent as $section) {
            foreach ($section['Fields'] ?? [] as $field) {
                $customId = $field['custom_id'] ?? null;
                if ($customId && array_key_exists($customId, $this->formBuilderRawData)) {
                    $responseData[$customId] = $this->formBuilderRawData[$customId];
                }
            }
        }

        /** @var Model $model */
        $model = $this->record;

        //We need to fetch the response and update it instead of creating a new one
        $model->response()->updateOrCreate([], [
            FormResponse::FORM_ID       => $formId,
            FormResponse::MODEL_TYPE    => get_class($model),
            FormResponse::MODEL_ID      => $model->getKey(),
            FormResponse::RESPONSE_DATA => $responseData,
        ]);
    }
}
