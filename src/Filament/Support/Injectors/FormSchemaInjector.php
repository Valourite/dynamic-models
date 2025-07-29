<?php

namespace Valourite\FormBuilder\Filament\Support\Injectors;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Illuminate\Database\Eloquent\Model;
use Valourite\FormBuilder\Filament\Support\Generators\FormGenerator;
use Valourite\FormBuilder\Models\Form;

/**
 * The user will use this class to inject the form schema into their form schema
 */
class FormSchemaInjector
{
    //TODO: see about using $get to get the record and grab he class
    public static function make($model): array
    {
        return [
            //The select that allows a user to select a form
            Select::make(Form::FORM_ID)
                ->label('Form')
                ->live()
                ->options(function (callable $get, ?Model $record) use ($model) {
                    return Form::query()
                        ->where(Form::FORM_MODEL, $model)
                        ->where(Form::IS_ACTIVE, true)
                        ->pluck(Form::FROM_NAME, Form::FORM_ID)
                        ->toArray();
                })
                ->afterStateHydrated(function (?Model $record, Component $component) {
                    $component->state($record?->response?->form_id);
                })
                ->required(),

            //The gorup that generates the form schema based on the selected form
            Group::make()
                ->schema(function (callable $get) {
                    $formId = $get(Form::FORM_ID);
                    if (!filled($formId)) {
                        return []; // return empty schema if no form selected
                    }

                    return FormGenerator::formSchema($formId);
                })
                ->visible(fn(callable $get) => filled($get(Form::FORM_ID)))
                ->columnSpanFull(),
        ];
    }
}