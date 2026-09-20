<?php

namespace JeffersonGoncalves\Filament\WidgetConfiguration;

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
        WidgetConfigurationPlugin::make()->apply();
    }
}
