<?php

namespace JeffersonGoncalves\Filament\WidgetConfiguration\Tests\Fixtures\Widgets;

use Filament\Widgets\StatsOverviewWidget;

class CustomStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '99s';
}
