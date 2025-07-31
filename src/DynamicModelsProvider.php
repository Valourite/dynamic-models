<?php

namespace Valourite\DynamicModels;

use Spatie\LaravelPackageTools\PackageServiceProvider;
use Valourite\DynamicModels\Commands\InstallDynamicModels;

final class DynamicModelsProvider extends PackageServiceProvider
{
    public function bootingPackage()
    {
        //fallback to ensure migrations run
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function configurePackage(\Spatie\LaravelPackageTools\Package $package): void
    {
        $package
            ->name('dynamic-models')
            //We do not have translations or views
            ->hasConfigFile()
            ->hasCommands($this->getCommands());
    }

    public function getCommands(): array
    {
        return [
            InstallDynamicModels::class,
        ];
    }
}
