<?php

namespace JeffersonGoncalves\Filament\WidgetConfiguration\Tests\Fixtures\Widgets;

use Filament\Widgets\Widget;

class CustomLazyWidget extends Widget
{
    protected static bool $isLazy = true;

    protected string $view = 'filament-widgets::table-widget';
}
