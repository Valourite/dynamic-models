<?php

namespace Valourite\FormBuilder\Filament\Support\Renderers;

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

final class FieldRenderer
{
    /** @var array<string, Closure> */
    private static array $renderMap = [];

    public static function render(string $type, ?string $fieldID = null): Component
    {
        $type = mb_strtolower($type);

        if (empty(static::$renderMap)) {
            static::buildRenderMap();
        }

        $renderer = static::$renderMap[$type] ?? static::$renderMap['default'];

        return $renderer($fieldID);
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
