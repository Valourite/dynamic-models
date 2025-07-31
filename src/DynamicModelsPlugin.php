<?php

namespace Valourite\DynamicModels;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Valourite\DynamicModels\Filament\Resources\ModelTypeResource\ModelTypeResource;

final class DynamicModelsPlugin implements Plugin
{
    public static function make()
    {
        return new self();
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            ModelTypeResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}

    public function getId(): string
    {
        return 'dynamic-models';
    }
}
