<?php

namespace App\Services\Quiniela;

use App\Models\Pool;
use App\Models\PoolParticipant;
use App\Models\Prediction;
use App\Models\RankingSnapshot;
use Illuminate\Support\Facades\DB;

class RankingService
{
    /**
     * Recalculate participant statistics and rankings for a given pool
     */
    public function recalculatePoolRankings(int $poolId): array
    {
        $pool = Pool::findOrFail($poolId);
        $participants = PoolParticipant::where('pool_id', $poolId)
            ->where('status', 'approved')
            ->get();

        foreach ($participants as $participant) {
            // Fetch all calculated predictions for this participant in this pool
            $predictions = Prediction::where('pool_id', $poolId)
                ->where('user_id', $participant->user_id)
                ->whereNotNull('calculated_at')
                ->get();

            $totalPoints = 0;
            $exactCount = 0;
            $correctCount = 0;
            $failedCount = 0;

            foreach ($predictions as $pred) {
                $totalPoints += $pred->points_awarded;
                $detail = $pred->scoring_detail;

                if (!empty($detail)) {
                    if ($detail['exact_score'] ?? false) {
                        $exactCount++;
                    } elseif (($detail['correct_winner'] ?? false) || ($detail['correct_draw'] ?? false)) {
                        $correctCount++;
                    } else {
                        $failedCount++;
                    }
                }
            }

            $participant->update([
                'total_points' => $totalPoints,
                'exact_scores_count' => $exactCount,
                'correct_results_count' => $correctCount,
                'failed_predictions_count' => $failedCount,
            ]);
        }

        // Now sort and apply rankings
        $rankedParticipants = PoolParticipant::where('pool_id', $poolId)
            ->where('status', 'approved')
            ->orderBy('total_points', 'desc')
            ->orderBy('exact_scores_count', 'desc')
            ->orderBy('correct_results_count', 'desc')
            ->orderBy('failed_predictions_count', 'asc')
            ->orderBy('joined_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $rank = 1;
        $updatedList = [];

        foreach ($rankedParticipants as $index => $part) {
            $prevRank = $part->current_rank;
            
            // If current_rank is null (first time), set previous_rank same as new rank
            $newPrevRank = $prevRank ?: $rank;

            $part->update([
                'previous_rank' => $newPrevRank,
                'current_rank' => $rank,
            ]);

            $updatedList[] = [
                'user_id' => $part->user_id,
                'rank' => $rank,
                'points' => $part->total_points,
            ];

            $rank++;
        }

        return $updatedList;
    }

    /**
     * Capture a ranking snapshot for a given pool
     */
    public function captureSnapshot(int $poolId): int
    {
        $participants = PoolParticipant::where('pool_id', $poolId)
            ->where('status', 'approved')
            ->whereNotNull('current_rank')
            ->get();

        $date = now()->toDateString();
        $count = 0;

        foreach ($participants as $part) {
            // Delete snapshot of same day if exists to avoid duplicates
            RankingSnapshot::updateOrCreate(
                [
                    'pool_id' => $poolId,
                    'user_id' => $part->user_id,
                    'snapshot_date' => $date,
                ],
                [
                    'total_points' => $part->total_points,
                    'rank' => $part->current_rank,
                ]
            );
            $count++;
        }

        return $count;
    }
}
