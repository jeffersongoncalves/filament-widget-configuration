<?php

namespace JeffersonGoncalves\Filament\WidgetConfiguration\Tests\Fixtures\Widgets;

use Filament\Widgets\ChartWidget;

class PlainChartWidget extends ChartWidget
{
    protected function getType(): string
    {
        return 'line';
    }
}
