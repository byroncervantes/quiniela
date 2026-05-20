<?php

namespace App\Services\Quiniela;

use App\Models\GameMatch;
use App\Models\Prediction;
use App\Models\ScoringRule;
use Illuminate\Support\Facades\Log;

class PredictionScoringService
{
    /**
     * Calculate and award points for a specific prediction based on actual match results
     */
    public function scorePrediction(Prediction $prediction, GameMatch $match): array
    {
        // If the match is not finished, no points yet
        if ($match->status !== 'finished') {
            return [
                'points' => 0,
                'scoring_detail' => null,
            ];
        }

        $tournamentId = $match->tournament_id;

        // Load active scoring rules for the tournament
        $rules = ScoringRule::where('tournament_id', $tournamentId)
            ->where('is_active', true)
            ->pluck('points', 'key')
            ->toArray();

        // Default points if rules are not defined in DB
        $exactScorePts = $rules['exact_score'] ?? 3;
        $correctWinnerPts = $rules['correct_winner'] ?? 1;
        $correctDrawPts = $rules['correct_draw'] ?? 1;

        $predictedHome = $prediction->predicted_home_score;
        $predictedAway = $prediction->predicted_away_score;

        $actualHome = $match->home_score;
        $actualAway = $match->away_score;

        // Check for exact score match
        $isExactScore = ($predictedHome === $actualHome && $predictedAway === $actualAway);

        // Determine actual and predicted outcomes: 1 = Home Win, 2 = Away Win, 0 = Draw
        $actualOutcome = $this->determineOutcome($actualHome, $actualAway);
        $predictedOutcome = $this->determineOutcome($predictedHome, $predictedAway);

        $points = 0;
        $appliedRules = [];

        if ($isExactScore) {
            $points = $exactScorePts;
            $appliedRules[] = 'exact_score';
        } elseif ($actualOutcome === $predictedOutcome) {
            if ($actualOutcome === 0) {
                $points = $correctDrawPts;
                $appliedRules[] = 'correct_draw';
            } else {
                $points = $correctWinnerPts;
                $appliedRules[] = 'correct_winner';
            }
        }

        // Support for penalty shootout winner in knockout stages
        $knockoutWinnerPts = $rules['knockout_winner'] ?? 0;
        $isKnockout = ($match->stage !== 'group_stage');
        $acertedKnockoutWinner = false;

        if ($isKnockout && $match->winner_team_id && $prediction->predicted_winner_team_id) {
            if ($match->winner_team_id == $prediction->predicted_winner_team_id) {
                $points += $knockoutWinnerPts;
                $appliedRules[] = 'knockout_winner';
                $acertedKnockoutWinner = true;
            }
        }

        $detail = [
            'exact_score' => $isExactScore,
            'correct_winner' => ($actualOutcome === $predictedOutcome && $actualOutcome !== 0),
            'correct_draw' => ($actualOutcome === $predictedOutcome && $actualOutcome === 0),
            'acerted_knockout_winner' => $acertedKnockoutWinner,
            'points' => $points,
            'rules_applied' => $appliedRules,
        ];

        return [
            'points' => $points,
            'scoring_detail' => $detail,
        ];
    }

    /**
     * Score all predictions for a given match
     */
    public function scoreMatchPredictions(int $matchId): int
    {
        $match = GameMatch::findOrFail($matchId);
        
        if ($match->status !== 'finished') {
            return 0;
        }

        $predictions = Prediction::where('match_id', $matchId)->get();
        $count = 0;

        foreach ($predictions as $prediction) {
            $scoring = $this->scorePrediction($prediction, $match);

            $prediction->update([
                'points_awarded' => $scoring['points'],
                'scoring_detail' => $scoring['scoring_detail'],
                'calculated_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * Helper to determine outcome
     */
    private function determineOutcome(?int $home, ?int $away): int
    {
        if ($home === null || $away === null) {
            return -1;
        }
        if ($home > $away) {
            return 1; // Home Win
        }
        if ($away > $home) {
            return 2; // Away Win
        }
        return 0; // Draw
    }
}
