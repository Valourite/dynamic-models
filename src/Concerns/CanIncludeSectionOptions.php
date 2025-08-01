<?php

namespace Valourite\DynamicModels\Concerns;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

/**
 * This trait will be used to include section specific options into the section options.
 */
trait CanIncludeSectionOptions
{
    public static function getColumnSpan()
    {
        return Toggle::make('column_span_full')
            ->label('Full Width Section')
            ->helperText('Toggle whether this section spans full width.');
    }

    public static function getColumnCount()
    {
        return Select::make('column_count')
            ->label('Column Count On Section')
            ->helperText('The number of columns for this section.')
            ->options([
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
            ]);
    }

    public static function getCollapsible()
    {
        return Toggle::make('is_collapsible')
            ->label('Collapsible')
            ->helperText('Toggle whether this section can be collapsed.');
    }
}
