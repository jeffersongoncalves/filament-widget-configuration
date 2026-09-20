<?php

namespace JeffersonGoncalves\Filament\WidgetConfiguration\Tests\Fixtures\Widgets;

use Filament\Widgets\ChartWidget;

class CustomChartWidget extends ChartWidget
{
    protected ?string $pollingInterval = '99s';

    protected function getType(): string
    {
        return 'line';
    }
}
