<?php

namespace App\Console\Commands;

use App\Services\Quiniela\MatchLockService;
use Illuminate\Console\Command;

class LockStartedMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quinmariscal:lock-started-matches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bloquea los pronósticos de los partidos que ya comenzaron o que están por comenzar según la configuración.';

    /**
     * Execute the console command.
     */
    public function handle(MatchLockService $lockService): int
    {
        $this->info('Iniciando escaneo de partidos para bloqueo...');
        $count = $lockService->lockStartedMatches();
        $this->info("Se bloquearon {$count} partidos con éxito.");
        return Command::SUCCESS;
    }
}
