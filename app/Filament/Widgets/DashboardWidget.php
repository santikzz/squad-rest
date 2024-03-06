<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\User;

class DashboardWidget extends BaseWidget
{

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {

        $userCounts = [];
        $groupCounts = [];
        for($i = 0; $i < 6; $i++){
            $startOfMonth = Carbon::now()->subMonths($i + 1)->startOfMonth();
            $endOfMonth = Carbon::now()->subMonths($i)->endOfMonth();
            $userCounts[$startOfMonth->format('F Y')] = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $groupCounts[$startOfMonth->format('F Y')] = Group::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        }

        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        
        $userCountLastMonth = User::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $groupCountLastMonth = Group::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        return [
            Stat::make('Total groups', Group::count().' groups')
                ->description($groupCountLastMonth.' groups created last month')
                ->descriptionIcon('heroicon-s-user-group')
                ->color('info')
                ->chart($groupCounts),
            Stat::make('Total users', User::count().' users')
                ->description($userCountLastMonth.' new users last month')
                ->descriptionIcon('heroicon-m-user')
                ->color('success')
                ->chart($userCounts)
        ];
    }
}
