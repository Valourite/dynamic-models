<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Valourite\DynamicModels\Filament\Support\Components\SectionRepeater;
use Valourite\DynamicModels\Models\ModelType;

final class ModelTypeForm
{
    private static ?array $modelOptions = null;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::modelTypeDetailSection(),
                self::modelTypeSchemaSection(),
            ])
            ->columns(1);
    }

    private static function modelTypeDetailSection(): Section
    {
        return Section::make('Model Type Details')
            ->columns(2)
            ->schema([
                TextInput::make(ModelType::MODEL_TYPE_NAME)
                    ->label('Model Type Name')
                    ->helperText('The unique name of this model.')
                    ->maxLength(255)
                    ->required(),

                RichEditor::make(ModelType::MODEL_TYPE_DESCRIPTION)
                    ->label('Model Type Description')
                    ->helperText('Enter the optional description of the model type.')
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                        ['h2', 'h3', 'alignStart', 'alignCenter', 'alignEnd'],
                        ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ]),

                Textarea::make(ModelType::MODEL_TYPE_CONFIRMATION_MESSAGE)
                    ->label('Record Confirmation Message')
                    ->default('Your record has been submitted successfully!')
                    ->helperText('Enter the optional confirmation message of the record.'),

                Toggle::make(ModelType::CAN_BE_CREATED)
                    ->default(true)
                    ->label('Can new records be created from this type?')
                    ->required(),

                Select::make(ModelType::MODEL_TYPE_PARENT_MODEL)
                    ->label('Parent Model')
                    ->options(self::getModelOptions())
                    ->required(),

                TextInput::make(ModelType::MODEL_TYPE_VERSION)
                    ->default('1.0.0')
                    //set to readonly to prevent the user changing versions from the current mask
                    ->readOnly()
                    ->mask('9.9.9')
                    ->prefix('v')
                    ->maxLength(10)
                    ->required(),
            ]);
    }

    private static function modelTypeSchemaSection(): Section
    {
        return Section::make('Model Type Creation')
            ->columns(1)
            ->schema([
                SectionRepeater::make(ModelType::MODEL_TYPE_SCHEMA)->collapsed(),
            ]);
    }

    private static function getModelOptions(): array
    {
        //Can't cache incase these values are changed
        return collect(config('dynamic-models.parent_models', []))
            ->mapWithKeys(fn ($class) => [$class => class_basename($class)])
            ->toArray();
    }
}
