<?php

namespace Valourite\FormBuilder\Filament\Support\Generators;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Valourite\FormBuilder\Filament\Support\Renderers\FieldRenderer;
use Valourite\FormBuilder\Models\Form;
use Valourite\FormBuilder\Models\FormResponse;

final class FormGenerator
{

    /**
     * Generates the form schema that can be appended to the models form
     * @param int|\Valourite\FormBuilder\Models\Form $form
     * @return array
     */
    public static function formSchema(int|Form $form): array
    {
        $form = $form instanceof Form ? $form : Form::findOrFail($form);

        //Get the form content
        $formContent = $form->form_content;

        $components = [];

        foreach ($formContent as $section) {
            $fields = [];

            foreach ($section['Fields'] ?? [] as $field) {
                //dd($field);

                $fieldID = $field['custom_id'];

                $name = $field['name'];

                $label = $field['label'] ?? Str::title($name);

                $type = $field['type'];

                $required = $field['required'] ?? false;

                $prefixIcon = $field['prefix_icon'] ?? null;

                $heroIcon = $prefixIcon ? Heroicon::from($prefixIcon) : null;

                // we need to pass through a unique identifier
                $component = FieldRenderer::render($type, $fieldID);

                $component
                    ->label($label)
                    ->required($required)
                    ->afterStateHydrated(function (Component $component, $state) use ($fieldID) {
                        $record = $component->getLivewire()?->record ?? null;
                        $response = $record?->response ?? null;

                        if (!$record || !$response) {
                            return;
                        }

                        $data = $response->response_data ?? [];
                        $component->state($data[$fieldID] ?? null);
                    });

                if (self::hasMethod($component, 'prefixIcon')) {
                    $component->prefixIcon($heroIcon);

                    // colour wont work as we need to convert it to tailwind
                    if (self::hasMethod($component, 'prefixIconColor')) {
                        $component->prefixIconColor('white');
                    }
                }

                if (self::hasMethod($component, 'options')) {
                    if (isset($field['options']) && $field['options'] != null) {
                        $component->options(
                            collect($field['options'])->mapWithKeys(fn($opt) => [
                                $opt['value'] => Str::title(str_replace('_', ' ', $opt['label'])),
                            ])->toArray()
                        );
                    }
                }

                $fields[] = $component;
            }

            // create the section
            if (!empty($fields)) {
                $components[] = Section::make($section['title'] ?? 'Section')
                    ->schema($fields)
                    ->collapsible();
            }
        }

        return $components;
    }

    /**
     * Generates the infolist schema that can be appended to the models infolist
     * @param int|\Valourite\FormBuilder\Models\FormResponse $formResponse
     * @return array
     */
    public static function infolistSchema(int|FormResponse $formResponse): array
    {
        $formResponse = $formResponse instanceof FormResponse
            ? $formResponse
            : FormResponse::findOrFail($formResponse);

        $formContent = $formResponse->form?->form_content ?? [];
        $responseData = $formResponse->response_data ?? [];

        $entries = [];

        foreach ($formContent as $section) {
            $sectionTitle = $section['title'] ?? 'Section';
            $fields = [];

            foreach ($section['Fields'] ?? [] as $field) {
                $fieldId = $field['custom_id'] ?? null;
                $label = $field['label'] ?? $field['name'] ?? 'Field';

                if (!$fieldId) {
                    continue;
                }

                $value = $responseData[$fieldId] ?? '-';

                $value = match ($field['type']) {
                    'boolean' => $value ? 'Yes' : 'No',
                    'date' => \Carbon\Carbon::parse($value)->format('Y-m-d'),
                    default => $value,
                };

                $fields[] = TextEntry::make($fieldId)
                    ->label($label)
                    ->state($value);
            }

            if (!empty($fields)) {
                $entries[] = Section::make($sectionTitle)
                    ->schema($fields)
                    ->columns(2);
            }
        }

        return $entries;
    }


    private static function hasMethod(Component $component, string $method): bool
    {
        return method_exists($component, $method);
    }
}
