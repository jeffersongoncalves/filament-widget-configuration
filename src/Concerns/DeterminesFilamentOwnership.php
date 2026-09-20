<?php

namespace JeffersonGoncalves\Filament\WidgetConfiguration\Concerns;

use ReflectionException;
use ReflectionProperty;

trait DeterminesFilamentOwnership
{
    /**
     * A property is safe to override globally only while it still comes
     * from Filament's own core class/trait untouched. If a widget subclass
     * has redeclared `$property` itself, its declaring class is that
     * subclass (not `Filament\Widgets\*`/`Filament\Support\*`), and it must
     * be left alone — this is the whole precedence rule the plugin exists
     * to implement.
     */
    protected function propertyBelongsToFilament(string $class, string $property): bool
    {
        try {
            $declaringClass = (new ReflectionProperty($class, $property))->getDeclaringClass()->getName();
        } catch (ReflectionException) {
            return false;
        }

        return str_starts_with($declaringClass, 'Filament\\Widgets\\')
            || str_starts_with($declaringClass, 'Filament\\Support\\');
    }
}
