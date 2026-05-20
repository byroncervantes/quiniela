<?php

namespace App\Services\Quiniela;

use App\Models\GameMatch;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Log;

class MatchLockService
{
    /**
     * Lock all matches that have started or are about to start based on configurations
     */
    public function lockStartedMatches(): int
    {
        $closeMinutes = intval(AppSetting::getValue('predictions_close_minutes_before_match', 0));
        
        // Find all matches that should be locked but aren't explicitly marked yet
        $matches = GameMatch::whereNotIn('status', ['finished', 'cancelled'])
            ->whereNull('predictions_locked_at')
            ->get();

        $lockedCount = 0;

        foreach ($matches as $match) {
            $lockTime = $match->starts_at->copy()->subMinutes($closeMinutes);

            if (now()->greaterThanOrEqualTo($lockTime)) {
                $match->update([
                    'predictions_locked_at' => now(),
                ]);
                $lockedCount++;
            }
        }

        return $lockedCount;
    }
}
