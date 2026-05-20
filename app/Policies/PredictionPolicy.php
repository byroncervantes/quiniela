<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Prediction;
use App\Models\GameMatch;
use App\Models\PoolParticipant;

class PredictionPolicy
{
    /**
     * Determine whether the user can view any predictions.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the prediction.
     */
    public function view(User $user, Prediction $prediction): bool
    {
        // Admins can see all predictions. Users can see their own.
        // Also, once a match is locked, maybe users can see each other's predictions? Yes, that's standard for transparency in pool games!
        if (in_array($user->role, ['super_admin', 'admin_quiniela', 'moderador'])) {
            return true;
        }

        return $prediction->user_id === $user->id || $prediction->match->isPredictionsLocked();
    }

    /**
     * Determine whether the user can create a prediction.
     */
    public function create(User $user, ?GameMatch $match = null, ?int $poolId = null): bool
    {
        if ($user->status !== 'active') {
            return false;
        }

        // If no match or pool is passed, allow general creation access
        if ($match === null || $poolId === null) {
            return true;
        }

        // Must not be locked
        if ($match->isPredictionsLocked()) {
            return false;
        }

        // Must be an approved participant of the pool
        $participant = PoolParticipant::where('pool_id', $poolId)
            ->where('user_id', $user->id)
            ->first();

        return $participant && $participant->status === 'approved';
    }

    /**
     * Determine whether the user can update the prediction.
     */
    public function update(User $user, Prediction $prediction): bool
    {
        if ($user->status !== 'active') {
            return false;
        }

        // Only owner
        if ($prediction->user_id !== $user->id) {
            return false;
        }

        // Must not be locked
        if ($prediction->match->isPredictionsLocked()) {
            return false;
        }

        // Must be approved participant
        $participant = PoolParticipant::where('pool_id', $prediction->pool_id)
            ->where('user_id', $user->id)
            ->first();

        return $participant && $participant->status === 'approved';
    }

    /**
     * Determine whether the user can delete the prediction.
     */
    public function delete(User $user, Prediction $prediction): bool
    {
        // Generally predictions are not deleted by participants, only updated. Admins can.
        return in_array($user->role, ['super_admin', 'admin_quiniela']);
    }
}
