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
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews()
            ->hasCommands($this->getCommands());
    }

    // public function getMigrations()
    // {
    //     //return list of migration names
    //     //TODO: Add other migrations
    //     //If we allow the migrations inside hasMigrations, they can be published, remember that
    //     return [
    //         'create_forms_table',
    //     ];
    // }

    public function getCommands(): array
    {
        return [
            InstallDynamicModels::class,
        ];
    }
}
