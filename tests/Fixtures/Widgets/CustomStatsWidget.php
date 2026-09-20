<?php

namespace JeffersonGoncalves\Filament\WidgetConfiguration\Tests\Fixtures\Widgets;

use Filament\Widgets\StatsOverviewWidget;

class CustomStatsWidget extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '99s';
}
