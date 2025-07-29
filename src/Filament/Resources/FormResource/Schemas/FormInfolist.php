<?php

namespace Valourite\FormBuilder\Filament\Resources\FormResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Valourite\FormBuilder\Filament\Support\Generators\FormGenerator;
use Valourite\FormBuilder\Models\Form;

final class FormInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Form Details')
                ->schema([
                    TextEntry::make(Form::FROM_NAME)->label('Form Name'),
                    TextEntry::make(Form::FORM_SLUG)->label('Slug'),
                    TextEntry::make(Form::FORM_DESCRIPTION)->label('Description')->html(),
                    TextEntry::make(Form::FORM_CONFIRMATION_MESSAGE)->label('Confirmation Message')->html(),
                    TextEntry::make(Form::FORM_VERSION)->label('Version'),
                    TextEntry::make(Form::FORM_MODEL)
                        ->label('Model')
                        ->formatStateUsing(fn ($state) => class_basename($state)),
                    TextEntry::make(Form::IS_ACTIVE)
                        ->label('Active')
                        ->badge()
                        ->color(fn ($state) => $state ? 'success' : 'gray')
                        ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                ])
                ->columns(2),

            Section::make('Form Preview')
                ->schema(function (Get $get) {
                    $record = $get('record');

                    // we return the schema and allow the user to play with it -> enter values, they wont be saved
                    return FormGenerator::formSchema($record);
                })
                ->visible(fn (Get $get) => filled($get('record')?->form_content))
                ->columnSpanFull()
                ->columns(1),
        ])->columns(1);
    }
}
