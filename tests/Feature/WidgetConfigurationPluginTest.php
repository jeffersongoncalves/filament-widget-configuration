<?php

use JeffersonGoncalves\Filament\WidgetConfiguration\Tests\Fixtures\Widgets\CustomChartWidget;
use JeffersonGoncalves\Filament\WidgetConfiguration\Tests\Fixtures\Widgets\CustomLazyWidget;
use JeffersonGoncalves\Filament\WidgetConfiguration\Tests\Fixtures\Widgets\CustomStatsWidget;
use JeffersonGoncalves\Filament\WidgetConfiguration\Tests\Fixtures\Widgets\PlainChartWidget;
use JeffersonGoncalves\Filament\WidgetConfiguration\Tests\Fixtures\Widgets\PlainStatsWidget;
use JeffersonGoncalves\Filament\WidgetConfiguration\Tests\Fixtures\Widgets\PlainWidget;
use JeffersonGoncalves\Filament\WidgetConfiguration\WidgetConfigurationPlugin;

function staticProperty(string $class, string $property): mixed
{
    $reflection = new ReflectionProperty($class, $property);
    $reflection->setAccessible(true);

    return $reflection->getValue();
}

it('applies the configured polling interval to widgets that have not customized it', function () {
    config()->set('filament-widget-configuration.polling_interval', '30s');

    WidgetConfigurationPlugin::make()->apply();

    expect(staticProperty(PlainStatsWidget::class, 'pollingInterval'))->toBe('30s')
        ->and(staticProperty(PlainChartWidget::class, 'pollingInterval'))->toBe('30s');
});

it('leaves widgets alone that already customized their own polling interval', function () {
    config()->set('filament-widget-configuration.polling_interval', '30s');

    WidgetConfigurationPlugin::make()->apply();

    expect(staticProperty(CustomStatsWidget::class, 'pollingInterval'))->toBe('99s')
        ->and(staticProperty(CustomChartWidget::class, 'pollingInterval'))->toBe('99s');
});

it('supports disabling polling globally via null', function () {
    config()->set('filament-widget-configuration.polling_interval', null);

    WidgetConfigurationPlugin::make()->apply();

    expect(staticProperty(PlainStatsWidget::class, 'pollingInterval'))->toBeNull();
});

it('prefers a fluent polling interval over the config value', function () {
    config()->set('filament-widget-configuration.polling_interval', '30s');

    WidgetConfigurationPlugin::make()->pollingInterval('1m')->apply();

    expect(staticProperty(PlainStatsWidget::class, 'pollingInterval'))->toBe('1m');
});

it('applies the configured lazy default to widgets that have not customized it', function () {
    config()->set('filament-widget-configuration.lazy', false);

    WidgetConfigurationPlugin::make()->apply();

    expect(staticProperty(PlainWidget::class, 'isLazy'))->toBeFalse();
});

it('leaves widgets alone that already customized their own lazy default', function () {
    config()->set('filament-widget-configuration.lazy', false);

    WidgetConfigurationPlugin::make()->apply();

    expect(staticProperty(CustomLazyWidget::class, 'isLazy'))->toBeTrue();
});

it('prefers a fluent lazy default over the config value', function () {
    config()->set('filament-widget-configuration.lazy', true);

    WidgetConfigurationPlugin::make()->lazy(false)->apply();

    expect(staticProperty(PlainWidget::class, 'isLazy'))->toBeFalse();
});
