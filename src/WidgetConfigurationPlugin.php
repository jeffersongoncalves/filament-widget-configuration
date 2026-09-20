<?php

namespace JeffersonGoncalves\Filament\WidgetConfiguration;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\Widget;
use JeffersonGoncalves\Filament\WidgetConfiguration\Concerns\DeterminesFilamentOwnership;
use ReflectionProperty;

class WidgetConfigurationPlugin implements Plugin
{
    use DeterminesFilamentOwnership;

    protected ?string $pollingInterval = null;

    protected bool $pollingIntervalIsSet = false;

    protected ?bool $lazy = null;

    public function getId(): string
    {
        return 'filament-widget-configuration';
    }

    public function register(Panel $panel): void
    {
        $this->apply();
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    /**
     * Global default for every widget's polling interval. Pass `null` to
     * disable polling by default.
     */
    public function pollingInterval(?string $interval): static
    {
        $this->pollingInterval = $interval;
        $this->pollingIntervalIsSet = true;

        return $this;
    }

    /**
     * Global default for `Widget::$isLazy`.
     */
    public function lazy(bool $condition = true): static
    {
        $this->lazy = $condition;

        return $this;
    }

    /**
     * Apply the resolved global defaults directly onto Filament's own
     * static properties (`CanPoll::$pollingInterval` on
     * `StatsOverviewWidget`/`ChartWidget`, `CanBeLazy::$isLazy` on `Widget`).
     *
     * Because PHP shares static-property storage down the inheritance chain
     * until a subclass redeclares the property, this needs to run only
     * once, at boot time, against Filament's base classes — every widget
     * that has NOT redeclared the property inherits the new default for
     * free, while any widget that HAS redeclared it keeps its own separate
     * storage untouched.
     */
    public function apply(): void
    {
        $pollingInterval = $this->resolvePollingInterval();

        $this->overrideStaticProperty(StatsOverviewWidget::class, 'pollingInterval', $pollingInterval);
        $this->overrideStaticProperty(ChartWidget::class, 'pollingInterval', $pollingInterval);
        $this->overrideStaticProperty(Widget::class, 'isLazy', $this->resolveLazy());
    }

    protected function resolvePollingInterval(): ?string
    {
        return $this->pollingIntervalIsSet
            ? $this->pollingInterval
            : config('filament-widget-configuration.polling_interval');
    }

    protected function resolveLazy(): bool
    {
        return $this->lazy ?? (bool) config('filament-widget-configuration.lazy', true);
    }

    protected function overrideStaticProperty(string $class, string $property, mixed $value): void
    {
        if (! $this->propertyBelongsToFilament($class, $property)) {
            return;
        }

        $reflection = new ReflectionProperty($class, $property);
        $reflection->setAccessible(true);
        $reflection->setValue(null, $value);
    }
}
