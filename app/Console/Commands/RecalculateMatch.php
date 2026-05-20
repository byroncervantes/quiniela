<?php

namespace App\Console\Commands;

use App\Services\Quiniela\PredictionScoringService;
use App\Services\Quiniela\RankingService;
use App\Models\GameMatch;
use App\Models\Pool;
use Illuminate\Console\Command;

class RecalculateMatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quinmariscal:recalculate-match {match_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula los puntos de todos los pronósticos para un partido específico y actualiza rankings.';

    /**
     * Execute the console command.
     */
    public function handle(PredictionScoringService $scoringService, RankingService $rankingService): int
    {
        $matchId = $this->argument('match_id');
        $match = GameMatch::find($matchId);

        if (!$match) {
            $this->error("No se encontró el partido con ID: {$matchId}");
            return Command::FAILURE;
        }

        if ($match->status !== 'finished') {
            $this->warn("El partido con ID: {$matchId} no está en estado 'finished' (terminado).");
        }

        $this->info("Recalculando pronósticos para el partido #{$match->match_number} ({$match->home_placeholder} vs {$match->away_placeholder})...");
        $count = $scoringService->scoreMatchPredictions($match->id);
        $this->info("Se recalcularon {$count} pronósticos.");

        // Recalculate ranking for all pools associated with the tournament
        $pools = Pool::where('tournament_id', $match->tournament_id)->get();
        foreach ($pools as $pool) {
            $this->info("Actualizando ranking para la quiniela: {$pool->name}...");
            $rankingService->recalculatePoolRankings($pool->id);
            $rankingService->captureSnapshot($pool->id);
        }

        $this->info("Proceso de recálculo finalizado con éxito.");
        return Command::SUCCESS;
    }
}
