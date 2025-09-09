<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Valourite\DynamicModels\Concerns\HasTableActions;
use Valourite\DynamicModels\Models\ModelType;

final class ModelTypeTable
{
    use HasTableActions;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make(ModelType::MODEL_TYPE_NAME)
                    ->label(config('dynamic-models.navigation.label', 'Model Type') . ' Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make(ModelType::MODEL_TYPE_DESCRIPTION)
                    ->label('Description')
                    ->html()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make(ModelType::MODEL_TYPE_CONFIRMATION_MESSAGE)
                    ->label('Confirmation Message')
                    ->html()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make(ModelType::CAN_BE_CREATED)
                    ->label('Active')
                    ->badge()
                    ->color(fn (bool $state) => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No'),

                TextColumn::make(ModelType::MODEL_TYPE_PARENT_MODEL)
                    ->label('Model')
                    ->formatStateUsing(fn ($state) => class_basename($state)),

                TextColumn::make(ModelType::MODEL_TYPE_VERSION)
                    ->label('Version')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make(ModelType::CAN_BE_CREATED)
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),

                SelectFilter::make(ModelType::MODEL_TYPE_PARENT_MODEL)
                    ->label('Model')
                    ->options(collect(config('dynamic-models.parent_models', []))
                        ->mapWithKeys(fn ($model) => [$model => class_basename($model)])),


                TrashedFilter::make(),
            ])
            ->recordActions(
                ActionGroup::make(static::getTableActions())
            )
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
