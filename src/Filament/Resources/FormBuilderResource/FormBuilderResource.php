<?php

namespace Valourite\FormBuilder\Filament\Resources\FormBuilderResource;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Valourite\FormBuilder\Filament\Support\Generators\FormSchemaGenerator;
use Valourite\FormBuilder\Models\Form;

abstract class FormBuilderResource extends Resource
{
    public static function form(Schema $schema): Schema
    {
        $model    = static::getModel();
        $instance = new $model();

        return $schema
            ->components([
                ...static::baseSchemaFields(),

                Select::make($instance->getFormIdColumn())
                    ->label('Form')
                    ->relationship(
                        name: 'form',
                        titleAttribute: 'form_name',
                        modifyQueryUsing: fn ($query) => $query
                            ->where('form_model', $model)
                            ->where('is_active', true)
                    )
                    // ->searchable() searching is annoying
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set) use ($instance) {
                        $form = Form::find($state);
                        if ($form) {
                            $set($instance->getFormContentColumn(), $form->form_content);
                            $set($instance->getFormResponseColumn(), []);
                            $set($instance->getFormVersionColumn(), $form->form_version);
                        }
                    }),

                Hidden::make($instance->getFormContentColumn())->dehydrated()->default([]),
                Hidden::make($instance->getFormVersionColumn())->dehydrated()->default([]),

                Group::make()
                    ->schema(fn (callable $get) => FormSchemaGenerator::formContent(
                        $get($instance->getFormContentColumn()) ?? [],
                        $get($instance->getFormResponseColumn()) ?? []
                    ))
                    ->visible(fn (callable $get) => filled($get($instance->getFormContentColumn())))
                    ->columnSpanFull(),

                Hidden::make($instance->getFormResponseColumn())->dehydrated()->default([]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        $model    = static::getModel();
        $instance = new $model();

        return $schema->components([
            ...static::baseInfolistFields(),

            Group::make()
                ->schema(function (Get $get) use ($instance) {
                    $record = $get('record');

                    if ( ! $record) {
                        return [];
                    }

                    // Decode form content and response
                    $formContent = is_array($record->{$instance->getFormContentColumn()})
                        ? $record->{$instance->getFormContentColumn()}
                        : json_decode($record->{$instance->getFormContentColumn()} ?? '[]', true);

                    $formResponse = is_array($record->{$instance->getFormResponseColumn()})
                        ? $record->{$instance->getFormResponseColumn()}
                        : json_decode($record->{$instance->getFormResponseColumn()} ?? '[]', true);

                    $entries = [];

                    foreach ($formContent as $section) {
                        $sectionTitle = $section['title'] ?? 'Section';
                        $fields       = [];

                        foreach ($section['Fields'] ?? [] as $field) {
                            $fieldId = $field['custom_id'] ?? null;
                            $label   = $field['label'] ?? $field['name'] ?? 'Field';
                            $value   = $formResponse[$fieldId] ?? '-';

                            if ( ! $fieldId) {
                                continue;
                            }

                            $fields[] = TextEntry::make($fieldId)
                                ->label($label)
                                ->state($value);
                        }

                        if ( ! empty($fields)) {
                            $entries[] = Section::make($sectionTitle)->schema($fields)->columns(2);
                        }
                    }

                    return $entries;
                })
                ->columnSpanFull()
                ->visible(fn (Get $get) => filled($get('record')?->{$instance->getFormContentColumn()})),
        ]);
    }

    // Implement this in your resource to define model-specific fields
    public static function baseSchemaFields(): array
    {
        return method_exists(static::class, 'customSchemaFields')
            ? static::customSchemaFields()
            : static::generateFieldsFromModel();
    }

    // Implement this in your resource to define model-specific infolist fields
    public static function baseInfolistFields(): array
    {
        return method_exists(static::class, 'customInfolistFields')
            ? static::customInfolistFields()
            : static::generateInfoListFieldsFromModel();
    }

    public static function generateFieldsFromModel(): array
    {
        $modelClass = static::getModel();
        $model      = new $modelClass();

        $fields = [];

        foreach ($model->getFillable() as $attribute) {
            if (in_array($attribute, ['created_at','updated_at'])) {
                continue;
            }

            $cast = $model->getCasts()[$attribute] ?? null;

            $field = match ($cast) {
                'boolean' => Toggle::make($attribute),
                'integer', 'float', 'decimal' => TextInput::make($attribute)->numeric(),
                'date', 'datetime' => DatePicker::make($attribute),
                'array', 'json' => Textarea::make($attribute),
                default => TextInput::make($attribute),
            };

            $fields[] = $field->label(Str::headline($attribute));
        }

        return $fields;
    }

    public static function generateInfoListFieldsFromModel(): array
    {
        $modelClass = static::getModel();
        $model      = new $modelClass();

        $fields = [];

        foreach ($model->getFillable() as $attribute) {

            $field = TextEntry::make($attribute);

            $fields[] = $field->label(Str::headline($attribute));
        }

        return $fields;
    }
}
