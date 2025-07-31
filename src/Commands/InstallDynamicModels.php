<?php

namespace Valourite\DynamicModels\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

final class InstallDynamicModels extends Command
{
    protected $signature = 'dynamic-models:install';

    protected $description = 'Install the Dynamic Models Package (publish config, run migrations)';

    public function handle(): int
    {
        $this->info('🔧 Installing Dynamic Models...');

        $this->publishConfigIfNeeded();
        $this->runMigrationsIfNeeded();

        $this->info('🎉 Dynamic Models installed successfully!');

        return self::SUCCESS;
    }

    protected function publishConfigIfNeeded(): void
    {
        $configPath = config_path('dynamic-models.php');

        if (File::exists($configPath)) {
            $this->warn('⚠️  Config file already exists: dynamic-models.php');
            if ($this->confirm('Do you want to overwrite it?', false)) {
                $this->callSilent('vendor:publish', [
                    '--tag'   => 'dynamic-models-config',
                    '--force' => true,
                ]);
                $this->info('✅ Config overwritten');
            } else {
                $this->info('✅ Using existing config');
            }
        } else {
            $this->callSilent('vendor:publish', [
                '--tag' => 'dynamic-models-config',
            ]);
            $this->info('✅ Config published');
        }
    }

    protected function runMigrationsIfNeeded(): void
    {
        if (Schema::hasTable(config('dynamic-models.table_prefix') . 'model_types')) {
            $this->warn('⚠️  Migrations already seem to be applied (' . config('dynamic-models.table_prefix') . 'model_types table exists)');
            if ( ! $this->confirm('Do you want to run migrations anyway?', false)) {
                $this->info('⏭️  Skipping migration');

                return;
            }
        }

        $this->call('migrate');
        $this->info('✅ Migrations executed');
    }
}
