<div class="filament-hidden">

![Filament Widget Configuration](https://raw.githubusercontent.com/jeffersongoncalves/filament-widget-configuration/3.x/art/jeffersongoncalves-filament-widget-configuration.png)

</div>

# Filament Widget Configuration

[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-support-FFDD00?style=flat-square&logo=buy-me-a-coffee&logoColor=black)](https://buymeacoffee.com/jeffersongoncalves)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/filament-widget-configuration.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-widget-configuration)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/filament-widget-configuration/tests.yml?branch=3.x&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/filament-widget-configuration/actions?query=workflow%3Atests+branch%3A3.x)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/filament-widget-configuration/pint.yml?branch=3.x&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/filament-widget-configuration/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3A3.x)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/filament-widget-configuration.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/filament-widget-configuration)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/filament-widget-configuration.svg?style=flat-square)](LICENSE.md)

Configure Filament's widget polling interval and lazy-loading default **once, globally**, without editing every widget class by hand — while never touching a widget that already customizes either property itself.

## Why this plugin exists

Filament hardcodes a default polling interval on every polling-capable widget (`protected ?string $pollingInterval = '5s';`) with no built-in way to change that default across an entire application. A [PR adding a global `Widget::configureUsing()` hook for this](https://github.com/filamentphp/filament/pull/20495) was closed by the Filament maintainer, [danharrin](https://github.com/danharrin), because baking a global `configureUsing()` callback into core would silently override any widget's own customized `$pollingInterval`, with no clean way to preserve precedence.

This plugin solves the same problem from outside core, as an opt-in third-party plugin, with a precedence rule that never overrides a widget that has customized the behavior itself — the exact guarantee the upstream PR couldn't offer.

In a large project, changing the default polling interval or lazy-loading behavior otherwise means opening every single widget class by hand. This plugin lets you set both once, in config or in `AppServiceProvider`, and every widget that hasn't customized its own behavior inherits the new default automatically.

## How the precedence rule works

Before overriding a property, the plugin checks — via `ReflectionProperty::getDeclaringClass()` — whether that property still comes from Filament's own base class/trait (`Filament\Widgets\*`, `Filament\Support\*`). If it does, it's safe to override. If a widget subclass has redeclared the property itself, its declaring class is that subclass, and the plugin leaves it untouched.

On Filament 5, `$isLazy` (`CanBeLazy`) is still a **static** property, so it's overridden once, at boot time, on Filament's base `Widget` class — every subclass that hasn't redeclared it inherits the new default for free. `$pollingInterval` (`CanPoll`), however, is a **non-static instance property**, so it can't be pre-set for an entire class hierarchy; instead, the plugin registers a [Livewire `ComponentHook`](https://livewire.laravel.com/docs/hooks) that applies the resolved default to each widget instance individually, on `mount`/`hydrate`, using the same declaring-class check per instance.

## Compatibility

| Branch | Filament |
|--------|----------|
| [1.x](https://github.com/jeffersongoncalves/filament-widget-configuration/tree/1.x) | 3.x |
| [2.x](https://github.com/jeffersongoncalves/filament-widget-configuration/tree/2.x) | 4.x |
| [3.x](https://github.com/jeffersongoncalves/filament-widget-configuration/tree/3.x) | 5.x |

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/filament-widget-configuration:"^3.0"
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=filament-widget-configuration-config
```

## Usage

By default the plugin **auto-applies** on every request using `config/filament-widget-configuration.php` — no panel wiring required.

```php
return [
    'polling_interval' => env('FILAMENT_WIDGET_CONFIGURATION_POLLING_INTERVAL', '5s'),
    'lazy' => env('FILAMENT_WIDGET_CONFIGURATION_LAZY', true),
];
```

Set `polling_interval` to `null` to disable polling by default across the app.

If you prefer to configure it in code, use `WidgetConfigurationPlugin` fluently — e.g. from `AppServiceProvider::boot()`, registered on your panel:

```php
use JeffersonGoncalves\Filament\WidgetConfiguration\WidgetConfigurationPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(
            WidgetConfigurationPlugin::make()
                ->pollingInterval('30s')
                ->lazy(false)
        );
}
```

Fluent values always win over the config file. Either way, a widget that already sets its own `$pollingInterval` or `$isLazy` keeps its own value.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
