<?php

use JeffersonGoncalves\Filament\WidgetConfiguration\Support\WidgetPollingIntervalHook;
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

function instanceProperty(object $object, string $property): mixed
{
    $reflection = new ReflectionProperty($object, $property);
    $reflection->setAccessible(true);

    return $reflection->getValue($object);
}

/**
 * Simulates a Livewire `mount` lifecycle event for the given widget instance,
 * the same way `Livewire\ComponentHookRegistry::boot()` does, without going
 * through a full Livewire::test() round-trip (which requires more of the
 * HTTP/session stack than this plugin's own logic needs).
 */
function mountWidgetPollingHook(object $widget): void
{
    $hook = new WidgetPollingIntervalHook;
    $hook->setComponent($widget);
    $hook->mount([], null);
}

it('applies the configured polling interval to widget instances that have not customized it', function () {
    config()->set('filament-widget-configuration.polling_interval', '30s');

    WidgetConfigurationPlugin::make()->apply();

    $stats = new PlainStatsWidget;
    $chart = new PlainChartWidget;

    mountWidgetPollingHook($stats);
    mountWidgetPollingHook($chart);

    expect(instanceProperty($stats, 'pollingInterval'))->toBe('30s')
        ->and(instanceProperty($chart, 'pollingInterval'))->toBe('30s');
});

it('leaves widget instances alone that already customized their own polling interval', function () {
    config()->set('filament-widget-configuration.polling_interval', '30s');

    WidgetConfigurationPlugin::make()->apply();

    $stats = new CustomStatsWidget;
    $chart = new CustomChartWidget;

    mountWidgetPollingHook($stats);
    mountWidgetPollingHook($chart);

    expect(instanceProperty($stats, 'pollingInterval'))->toBe('99s')
        ->and(instanceProperty($chart, 'pollingInterval'))->toBe('99s');
});

it('supports disabling polling globally via null', function () {
    config()->set('filament-widget-configuration.polling_interval', null);

    WidgetConfigurationPlugin::make()->apply();

    $stats = new PlainStatsWidget;

    mountWidgetPollingHook($stats);

    expect(instanceProperty($stats, 'pollingInterval'))->toBeNull();
});

it('prefers a fluent polling interval over the config value', function () {
    config()->set('filament-widget-configuration.polling_interval', '30s');

    WidgetConfigurationPlugin::make()->pollingInterval('1m')->apply();

    $stats = new PlainStatsWidget;

    mountWidgetPollingHook($stats);

    expect(instanceProperty($stats, 'pollingInterval'))->toBe('1m');
});

it('ignores non-widget Livewire components', function () {
    config()->set('filament-widget-configuration.polling_interval', '30s');

    WidgetConfigurationPlugin::make()->apply();

    $hook = new WidgetPollingIntervalHook;
    $hook->setComponent(new stdClass);

    expect(fn () => $hook->mount([], null))->not->toThrow(Throwable::class);
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
