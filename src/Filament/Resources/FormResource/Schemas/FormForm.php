<?php

namespace Valourite\FormBuilder\Filament\Resources\FormResource\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Valourite\FormBuilder\Filament\Support\Components\SectionRepeater;
use Valourite\FormBuilder\Models\Form;

final class FormForm
{
    protected static ?array $modelOptions = null;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::formDetailsSection(),
                self::formContentSection(),
            ])
            ->columns(1);
    }

    protected static function formDetailsSection(): Section
    {
        return Section::make('Form Details')
            ->columns(2)
            ->schema([
                TextInput::make(Form::FROM_NAME)
                    ->label('Form Name')
                    ->helperText('The unique name of the form')
                    ->maxLength(255)
                    ->live(debounce: 500) // reduces chatter
                    ->afterStateUpdated(function (Set $set, ?string $state, $context) {
                        if ($context !== 'edit') {
                            $set(Form::FORM_SLUG, Str::slug($state));
                        }
                    })
                    ->required(),

                TextInput::make(Form::FORM_SLUG)
                    ->label('Form Slug')
                    ->maxLength(255)
                    ->rules(['alpha_dash'])
                    ->required()
                    ->helperText('The slug of the form'),

                RichEditor::make(Form::FORM_DESCRIPTION)
                    ->label('Form Description')
                    ->helperText('Enter the optional description of the form')
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                        ['h2', 'h3', 'alignStart', 'alignCenter', 'alignEnd'],
                        ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ]),

                Textarea::make(Form::FORM_CONFIRMATION_MESSAGE)
                    ->label('Form Confirmation Message')
                    ->default('Your form has been submitted successfully!')
                    ->helperText('Enter the optional confirmation message of the form'),

                Toggle::make(Form::IS_ACTIVE)
                    ->default(true)
                    ->label('Is Form Active?')
                    ->required(),

                Select::make(Form::FORM_MODEL)
                    ->label('Form Model')
                    ->options(self::getModelOptions())
                    ->required(),

                TextInput::make(Form::FORM_VERSION)
                    ->default('1.0.0')
                    ->mask('9.9.9')
                    ->prefix('v')
                    ->maxLength(10)
                    ->required(),
            ]);
    }

    protected static function formContentSection(): Section
    {
        return Section::make('Form Creation')
            ->columns(1)
            ->schema([
                SectionRepeater::make(Form::FORM_CONTENT)->collapsed(),
            ]);
    }

    protected static function getModelOptions(): array
    {
        return static::$modelOptions ??= Cache::remember('form-builder.model-options', now()->addHours(6), function () {
            return collect(config('form-builder.models', []))
                ->mapWithKeys(fn ($class) => [$class => class_basename($class)])
                ->toArray();
        });
    }
}