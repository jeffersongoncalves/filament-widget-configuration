## Filament Widget Configuration

Sets Filament's widget `$pollingInterval` and `$isLazy` defaults globally, without editing every widget class — while leaving any widget that already customizes either property untouched.

### Installation

@verbatim
<code-snippet name="Install the plugin" lang="bash">
composer require jeffersongoncalves/filament-widget-configuration
</code-snippet>
@endverbatim

### Configuration

The plugin auto-applies from `config/filament-widget-configuration.php` — no panel wiring required.

@verbatim
<code-snippet name="config/filament-widget-configuration.php" lang="php">
return [
    'polling_interval' => env('FILAMENT_WIDGET_CONFIGURATION_POLLING_INTERVAL', '5s'),
    'lazy' => env('FILAMENT_WIDGET_CONFIGURATION_LAZY', true),
];
</code-snippet>
@endverbatim

Set `polling_interval` to `null` to disable polling by default.

### Fluent configuration (optional)

@verbatim
<code-snippet name="Register in PanelProvider" lang="php">
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
</code-snippet>
@endverbatim

### Precedence rule

A widget that already declares its own `$pollingInterval` or `$isLazy` is never touched — the plugin only overrides the value when the property still comes from Filament's own base widget class.

### Best Practices

- Prefer the config file for a single global default; use the fluent API only when a specific panel needs a different value than the rest of the app.
- Do not rely on this plugin to change a widget's *own* customized polling interval — that customization always wins by design.
