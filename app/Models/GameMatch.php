<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'tournament_group_id',
        'home_team_id',
        'away_team_id',
        'home_placeholder',
        'away_placeholder',
        'stage', // group_stage, round_of_32, round_of_16, quarter_final, semi_final, third_place, final
        'match_number',
        'stadium',
        'city',
        'country',
        'starts_at',
        'status', // scheduled, live, finished, cancelled
        'home_score',
        'away_score',
        'home_penalty_score',
        'away_penalty_score',
        'winner_team_id',
        'predictions_locked_at',
        'result_locked_at',
        'result_entered_by',
        'result_entered_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'predictions_locked_at' => 'datetime',
        'result_locked_at' => 'datetime',
        'result_entered_at' => 'datetime',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TournamentGroup::class, 'tournament_group_id');
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function winnerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    public function resultEnteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'result_entered_by');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class, 'match_id');
    }

    /**
     * Check if predictions are locked for this match
     */
    public function isPredictionsLocked(): bool
    {
        if ($this->status === 'finished' || $this->status === 'cancelled') {
            return true;
        }

        // If there is a manual lock date/time set, check if that time has passed
        if ($this->predictions_locked_at) {
            return now()->greaterThanOrEqualTo($this->predictions_locked_at);
        }

        // Lock based on match start time (or close setting)
        $closeMinutes = intval(AppSetting::getValue('predictions_close_minutes_before_match', 0));
        $lockTime = $this->starts_at->copy()->subMinutes($closeMinutes);

        return now()->greaterThanOrEqualTo($lockTime);
    }
}
