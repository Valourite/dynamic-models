<?php

namespace Valourite\FormBuilder;

use Spatie\LaravelPackageTools\PackageServiceProvider;
use Valourite\FormBuilder\Commands\InstallFormBuilder;

final class FormBuilderProvider extends PackageServiceProvider
{
    public function bootingPackage()
    {
        //fallback to ensure migrations run
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function configurePackage(\Spatie\LaravelPackageTools\Package $package): void
    {
        $package
            ->name('form-builder')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews()
            ->hasCommands($this->getCommands())
            ->hasMigrations($this->getMigrations());

        // Cache configuration
        $this->app->singleton('form-builder.config', function () {
            return cache()->rememberForever('form-builder.config', function () {
                return config('form-builder');
            });
        });
    }

    public function getMigrations()
    {
        //return list of migration names
        return [
            'create_forms_table',
        ];
    }

    public function getCommands(): array
    {
        return [
            InstallFormBuilder::class,
        ];
    }
}
