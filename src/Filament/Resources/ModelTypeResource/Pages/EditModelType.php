<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Valourite\DynamicModels\Contracts\StrategyInterface;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\ModelTypeResource;
use Valourite\DynamicModels\Models\ModelType;
use Valourite\DynamicModels\Support\DefaultStrategy;

final class EditModelType extends EditRecord
{
    protected static string $resource = ModelTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // dd($this->data[ModelType::MODEL_TYPE_SCHEMA]);
        //Set the schema data to the schema data provided by $this->data as it contains
        //the missing action data that gets removed from $form->getState()
        $data[ModelType::MODEL_TYPE_SCHEMA] = $this->data[ModelType::MODEL_TYPE_SCHEMA];

        $record = $this->getRecord();

        // Run BEFORE-SAVE HOOKS
        foreach (config('dynamic-models.hooks.before_save', []) as $hookClass) {
            /** @var \Valourite\DynamicModels\Contracts\BeforeSaveEditHookInterface $hook */
            $hook = App::make($hookClass);
            $data = $hook->beforeSave($record, $data);
        }

        // determine if versioning is enabled,
        // if not, allow all changes to be made to record without restriction
        if (config('dynamic-models.versioning.enabled', true)) {
            // get the current strategy
            /** @var StrategyInterface $strategy */
            $strategy = App::make(config('dynamic-models.versioning.strategy', DefaultStrategy::class));

            // determine if schema has changed
            $diff = $strategy->hasSchemaChanged(
                $record->model_type_schema,
                $data[ModelType::MODEL_TYPE_SCHEMA]
            );

            $data = $diff ? $strategy->onSchemaChanged($record, $data) : $strategy->onSchemaRemained($record, $data);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        // Run AFTER-SAVE HOOKS (new version path)
        foreach (config('dynamic-models.hooks.after_save', []) as $hookClass) {
            /** @var \Valourite\DynamicModels\Contracts\AfterSaveEditHookInterface $hook */
            $hook = App::make($hookClass);
            $hook->afterSave($record, $data, config('dynamic-models.versioning.create_new', true));
        }

        return $record;
    }
}
