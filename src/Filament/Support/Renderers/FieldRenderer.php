<?php

namespace Valourite\DynamicModels\Filament\Support\Renderers;

use Closure;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Component;
use Valourite\DynamicModels\Concerns\HandlesFieldOptions;
use Valourite\DynamicModels\Filament\Enums\FieldType;

final class FieldRenderer
{
    use HandlesFieldOptions;

    /** @var array<string, Closure> */
    private static array $renderMap = [];

    public static function render(string $type, ?string $fieldID = null, array $options = []): Component
    {
        $type = mb_strtolower($type);

        if (empty(static::$renderMap)) {
            static::buildRenderMap();
        }

        $renderer  = static::$renderMap[$type] ?? static::$renderMap['default'];
        $component = $renderer($fieldID);

        return static::applyFieldOptions($component, $type, $options);
    }

    /**
     * Check if a component supports certain features.
     */
    public static function supportsFeature(string|FieldType $type, string $feature): bool
    {
        $feature = mb_strtolower($feature);
        $type    = is_string($type) ? mb_strtolower($type) : mb_strtolower($type->value);

        // Lists of field types that support each feature
        $featureSupport = [
            // Text-related features
            'prefix'      => ['text', 'number', 'email', 'password'],
            'suffix'      => ['text', 'number', 'email', 'password'],
            'icon'        => ['text', 'number', 'email', 'password', 'select', 'date', 'time', 'datetime', 'file'],
            'placeholder' => ['text', 'number', 'email', 'password', 'textarea'],
            'maxlength'   => ['text', 'email', 'password', 'textarea'],

            // Selection-related features
            'options'    => ['select', 'radio'],
            'searchable' => ['select'],
            'multiple'   => ['select', 'file'],
            'inline'     => ['radio', 'checkbox'],

            // Number-specific features
            'min_value' => ['number'],
            'max_value' => ['number'],
            'step'      => ['number'],

            // Text area specific features
            'rows'     => ['textarea'],
            'cols'     => ['textarea'],
            'autosize' => ['textarea'],

            // Date and time features
            'date_format' => ['date', 'datetime'],
            'min_date'    => ['date', 'datetime'],
            'max_date'    => ['date', 'datetime'],

            // File upload features
            'file_upload'         => ['file'],
            'accepted_file_types' => ['file'],
            'max_file_size'       => ['file'],
            'max_files'           => ['file'],
        ];

        // Check if the feature is supported for this field type
        if (isset($featureSupport[$feature])) {
            return in_array($type, $featureSupport[$feature], true);
        }

        return false;
    }

    private static function buildRenderMap(): void
    {
        static::$renderMap = [
            'text'     => fn ($id) => TextInput::make($id),
            'number'   => fn ($id) => TextInput::make($id)->numeric(),
            'password' => fn ($id) => TextInput::make($id)->password()->revealable(),
            'email'    => fn ($id) => TextInput::make($id)->email(),
            'textarea' => fn ($id) => Textarea::make($id),
            'select'   => fn ($id) => Select::make($id),
            'radio'    => fn ($id) => Radio::make($id),
            'checkbox' => fn ($id) => Checkbox::make($id),
            'date'     => fn ($id) => DatePicker::make($id),
            'time'     => fn ($id) => TimePicker::make($id),
            'datetime' => fn ($id) => DateTimePicker::make($id),
            // 'file'  => fn($id) => FileUpload::make($id), // TODO: implement
            'default' => fn ($id) => TextInput::make($id),
        ];
    }
}
