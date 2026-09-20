---
name: filament-widget-configuration-development
description: Configure global Filament widget polling interval and lazy-loading defaults, and understand the precedence rule that protects customized widgets.
---

# Filament Widget Configuration Development

## When to use this skill

Use this skill when:
- Changing the default polling interval for every widget in an app
- Changing the default lazy-loading behavior for every widget in an app
- Debugging why a specific widget did (or did not) pick up the global default

## Configuration

### Basic Setup

```php
use JeffersonGoncalves\Filament\WidgetConfiguration\WidgetConfigurationPlugin;

WidgetConfigurationPlugin::make()
    ->pollingInterval('30s') // or null to disable polling by default
    ->lazy(false);
```

Register the plugin on a panel to apply it there, or rely on `config/filament-widget-configuration.php` for an app-wide default with no panel wiring.

### Config file

```php
return [
    'polling_interval' => env('FILAMENT_WIDGET_CONFIGURATION_POLLING_INTERVAL', '5s'),
    'lazy' => env('FILAMENT_WIDGET_CONFIGURATION_LAZY', true),
];
```

Fluent values (via `WidgetConfigurationPlugin::make()->pollingInterval()/->lazy()`) always win over the config file.

## The precedence rule

The plugin uses `ReflectionProperty::getDeclaringClass()` to check where `$pollingInterval`/`$isLazy` is actually declared for a given widget:

- Declaring class under `Filament\Widgets\*` or `Filament\Support\*` → still Filament's own default → safe to override.
- Declaring class is anything else (a widget subclass redeclared the property) → leave it alone.

This means a widget that sets its own `protected ?string $pollingInterval = '10s';` always keeps `10s`, regardless of the global default.

On this branch, `$pollingInterval` is a non-static instance property, so the check (and override) happens per widget instance, via a Livewire `ComponentHook` registered by the plugin's service provider. `$isLazy` is still static, so it's overridden once at boot time on Filament's base `Widget` class.

## Troubleshooting

### A widget didn't pick up the global default

**Cause**: The widget (or one of its ancestors up to, but not including, Filament's own base widget class) redeclares `$pollingInterval` or `$isLazy` itself.

**Solution**: This is by design — remove the widget's own declaration if you want it to inherit the global default instead.

### Global default not applied at all

**Cause**: The plugin's service provider didn't boot, or the config file wasn't published/cached correctly.

**Solution**: Confirm `JeffersonGoncalves\Filament\WidgetConfiguration\WidgetConfigurationServiceProvider` is discovered (`composer show jeffersongoncalves/filament-widget-configuration`) and re-run `php artisan config:clear` after changing `.env`.
