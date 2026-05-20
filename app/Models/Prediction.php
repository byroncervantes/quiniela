<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'pool_id',
        'user_id',
        'match_id',
        'predicted_home_score',
        'predicted_away_score',
        'predicted_home_penalty_score',
        'predicted_away_penalty_score',
        'predicted_winner_team_id',
        'points_awarded',
        'scoring_detail',
        'submitted_at',
        'locked_at',
        'calculated_at',
    ];

    protected $casts = [
        'scoring_detail' => 'array',
        'submitted_at' => 'datetime',
        'locked_at' => 'datetime',
        'calculated_at' => 'datetime',
    ];

    public function pool(): BelongsTo
    {
        return $this->belongsTo(Pool::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function predictedWinner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'predicted_winner_team_id');
    }
}
