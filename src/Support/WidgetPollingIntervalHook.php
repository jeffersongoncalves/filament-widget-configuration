<?php

namespace JeffersonGoncalves\Filament\WidgetConfiguration\Support;

use Filament\Widgets\Widget;
use JeffersonGoncalves\Filament\WidgetConfiguration\Concerns\DeterminesFilamentOwnership;
use JeffersonGoncalves\Filament\WidgetConfiguration\WidgetConfigurationPlugin;
use Livewire\ComponentHook;
use ReflectionProperty;

/**
 * On Filament 4/5, `CanPoll::$pollingInterval` is a non-static instance
 * property, so the global default can only be applied per widget instance,
 * at runtime — this hook does that on every widget mount/hydrate, while
 * still respecting the plugin's precedence rule (see
 * `DeterminesFilamentOwnership`).
 */
class WidgetPollingIntervalHook extends ComponentHook
{
    use DeterminesFilamentOwnership;

    public function mount($params, $parent): void
    {
        $this->applyPollingInterval();
    }

    public function hydrate($memo): void
    {
        $this->applyPollingInterval();
    }

    protected function applyPollingInterval(): void
    {
        $component = $this->component;

        if (! $component instanceof Widget) {
            return;
        }

        if (! $this->propertyBelongsToFilament($component::class, 'pollingInterval')) {
            return;
        }

        $property = new ReflectionProperty($component, 'pollingInterval');
        $property->setAccessible(true);
        $property->setValue($component, WidgetConfigurationPlugin::appliedPollingInterval());
    }
}
