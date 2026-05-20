<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoolParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'pool_id',
        'user_id',
        'status', // pending, approved, rejected, blocked
        'joined_at',
        'approved_at',
        'approved_by',
        'total_points',
        'exact_scores_count',
        'correct_results_count',
        'failed_predictions_count',
        'current_rank',
        'previous_rank',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function pool(): BelongsTo
    {
        return $this->belongsTo(Pool::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
