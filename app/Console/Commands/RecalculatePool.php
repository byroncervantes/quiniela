<?php

namespace App\Console\Commands;

use App\Services\Quiniela\RankingService;
use App\Models\Pool;
use Illuminate\Console\Command;

class RecalculatePool extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quinmariscal:recalculate-pool {pool_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula el ranking completo y genera un snapshot para una quiniela específica.';

    /**
     * Execute the console command.
     */
    public function handle(RankingService $rankingService): int
    {
        $poolId = $this->argument('pool_id');
        $pool = Pool::find($poolId);

        if (!$pool) {
            $this->error("No se encontró la quiniela con ID: {$poolId}");
            return Command::FAILURE;
        }

        $this->info("Recalculando ranking para la quiniela: {$pool->name}...");
        $list = $rankingService->recalculatePoolRankings($pool->id);
        $this->info("Se recalcularon posiciones para " . count($list) . " participantes.");

        $this->info("Tomando snapshot histórico del ranking...");
        $snapshots = $rankingService->captureSnapshot($pool->id);
        $this->info("Snapshot generado para {$snapshots} usuarios.");

        $this->info("¡Completado!");
        return Command::SUCCESS;
    }
}
