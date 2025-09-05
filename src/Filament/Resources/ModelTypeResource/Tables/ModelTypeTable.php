<?php

namespace Valourite\DynamicModels\Filament\Resources\ModelTypeResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Valourite\DynamicModels\Models\ModelType;

final class ModelTypeTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make(ModelType::MODEL_TYPE_NAME)
                    ->label('Model Type Name')
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

                TextColumn::make('parent.model_type_name')
                    ->label('Parent')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
