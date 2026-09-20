<?php

namespace JeffersonGoncalves\Filament\WidgetConfiguration;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Widgets\Widget;
use JeffersonGoncalves\Filament\WidgetConfiguration\Concerns\DeterminesFilamentOwnership;
use ReflectionProperty;

class WidgetConfigurationPlugin implements Plugin
{
    use DeterminesFilamentOwnership;

    /**
     * The resolved polling interval, read at runtime by
     * `Support\WidgetPollingIntervalHook` — on Filament 4/5,
     * `CanPoll::$pollingInterval` is a non-static instance property, so it
     * can only be overridden per widget instance, not once at boot time.
     */
    protected static ?string $appliedPollingInterval = '5s';

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
     * `Widget::$isLazy` is still static on Filament 4/5, so it is overridden
     * directly, once, the same way as on Filament 3. `$pollingInterval` is
     * no longer static, so it is only resolved here and applied per widget
     * instance by `Support\WidgetPollingIntervalHook`.
     */
    public function apply(): void
    {
        static::$appliedPollingInterval = $this->resolvePollingInterval();

        $this->overrideStaticProperty(Widget::class, 'isLazy', $this->resolveLazy());
    }

    public static function appliedPollingInterval(): ?string
    {
        return static::$appliedPollingInterval;
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
