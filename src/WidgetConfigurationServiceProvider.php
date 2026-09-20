<?php

namespace JeffersonGoncalves\Filament\WidgetConfiguration;

use JeffersonGoncalves\Filament\WidgetConfiguration\Support\WidgetPollingIntervalHook;
use Livewire\LivewireManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WidgetConfigurationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-widget-configuration')
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        app(LivewireManager::class)->componentHook(WidgetPollingIntervalHook::class);

        WidgetConfigurationPlugin::make()->apply();
    }
}
