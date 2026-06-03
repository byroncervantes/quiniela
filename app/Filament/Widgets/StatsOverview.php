<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Pool;
use App\Models\GameMatch;
use App\Models\Prediction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $activePools = Pool::where('is_active', true)->count();
        
        $totalMatches = GameMatch::count();
        $finishedMatches = GameMatch::where('status', 'finished')->count();
        
        $totalPredictions = Prediction::count();

        return [
            Stat::make('Usuarios Registrados', $totalUsers)
                ->description('Colaboradores en el sistema')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
                
            Stat::make('Quinielas Activas', $activePools)
                ->description('Grupos de quiniela activos')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('primary'),
                
            Stat::make('Partidos del Torneo', "{$finishedMatches} / {$totalMatches}")
                ->description('Partidos finalizados del Mundial')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
                
            Stat::make('Pronósticos Enviados', $totalPredictions)
                ->description('Predicciones totales de los usuarios')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
        ];
    }
}
