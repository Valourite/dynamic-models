<?php

namespace Valourite\FormBuilder\Filament\Resources\FormResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Valourite\FormBuilder\Filament\Resources\FormResource\FormResource;
use Valourite\FormBuilder\Models\Form;

final class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        $recordForm = $record->form_content;
        $dataForm   = $data['form_content'];

        $diff = static::hasFormContentChanged($recordForm, $dataForm);

        //We need to compare to arrays to see if they're identical
        if ($diff) {
            $newForm = $record->replicate([
                Form::FORM_ID,
                Form::FORM_MODEL,
                'created_at',
                'updated_at',
            ]);

            $newForm->form_content              = $data['form_content'];
            $newForm->form_description          = $data['form_description'];
            $newForm->form_confirmation_message = $data['form_confirmation_message'];
            $newForm->form_slug                 = $data['form_slug'];
            $newForm->is_active                 = $data['is_active'];
            $newForm->form_version              = $this->incrementVersion(
                $record->form_version,
                config('form-builder.increment_count', '0.0.1')
            );
            $newForm->save();

            // new form has been created, revert the data back to original
            $data['form_content'] = $record->form_content;

            // //redirect to the new form view page
            // return redirect(FormResource::getUrl('edit', ['record' => $newForm]));
        } else {
            // increment form version
            $data['form_version'] = $this->incrementVersion(
                $record->form_version,
                config('form-builder.increment_count', '0.0.1')
            );
        }

        return $data;
    }

    protected function incrementVersion(string $currentVersion, string $increment = '0.0.1'): string
    {
        [$major, $minor, $patch]          = array_map('intval', explode('.', $currentVersion));
        [$incMajor, $incMinor, $incPatch] = array_map('intval', explode('.', $increment));

        $newPatch = $patch + $incPatch;
        $newMinor = $minor + $incMinor;
        $newMajor = $major + $incMajor;

        return "{$newMajor}.{$newMinor}.{$newPatch}";
    }

    protected function hasFormContentChanged(array $old, array $new): bool
    {
        $normalize = fn (array $content) => collect($content)
            ->map(function ($section) {
                // Strip metadata
                unset($section['title'], $section['prefix_icon']);

                // Normalize fields
                $section['Fields'] = collect($section['Fields'] ?? [])
                    ->map(function ($field) {
                        unset($field['label'], $field['prefix_icon']);

                        return $field;
                    })
                    // Sort fields by custom_id for consistent structure
                    ->sortBy('custom_id')
                    ->values()
                    ->toArray();

                return $section;
            })
            // Sort sections by custom_id for consistent comparison
            ->sortBy('custom_id')
            ->values()
            ->toArray();

        return md5(json_encode($normalize($old))) !== md5(json_encode($normalize($new)));
    }
}
