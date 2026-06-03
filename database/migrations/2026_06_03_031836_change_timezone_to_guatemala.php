<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tournaments
        DB::table('tournaments')->update([
            'starts_at' => DB::raw('DATE_SUB(starts_at, INTERVAL 6 HOUR)'),
            'ends_at' => DB::raw('DATE_SUB(ends_at, INTERVAL 6 HOUR)'),
            'created_at' => DB::raw('DATE_SUB(created_at, INTERVAL 6 HOUR)'),
            'updated_at' => DB::raw('DATE_SUB(updated_at, INTERVAL 6 HOUR)'),
        ]);

        // 2. Matches
        DB::table('matches')->update([
            'starts_at' => DB::raw('DATE_SUB(starts_at, INTERVAL 6 HOUR)'),
            'predictions_locked_at' => DB::raw('DATE_SUB(predictions_locked_at, INTERVAL 6 HOUR)'),
            'result_locked_at' => DB::raw('DATE_SUB(result_locked_at, INTERVAL 6 HOUR)'),
            'result_entered_at' => DB::raw('DATE_SUB(result_entered_at, INTERVAL 6 HOUR)'),
            'created_at' => DB::raw('DATE_SUB(created_at, INTERVAL 6 HOUR)'),
            'updated_at' => DB::raw('DATE_SUB(updated_at, INTERVAL 6 HOUR)'),
        ]);

        // 3. Pools
        DB::table('pools')->update([
            'starts_at' => DB::raw('DATE_SUB(starts_at, INTERVAL 6 HOUR)'),
            'ends_at' => DB::raw('DATE_SUB(ends_at, INTERVAL 6 HOUR)'),
            'created_at' => DB::raw('DATE_SUB(created_at, INTERVAL 6 HOUR)'),
            'updated_at' => DB::raw('DATE_SUB(updated_at, INTERVAL 6 HOUR)'),
        ]);

        // 4. Pool Participants
        DB::table('pool_participants')->update([
            'joined_at' => DB::raw('DATE_SUB(joined_at, INTERVAL 6 HOUR)'),
            'approved_at' => DB::raw('DATE_SUB(approved_at, INTERVAL 6 HOUR)'),
        ]);

        // 5. Users
        DB::table('users')->update([
            'created_at' => DB::raw('DATE_SUB(created_at, INTERVAL 6 HOUR)'),
            'updated_at' => DB::raw('DATE_SUB(updated_at, INTERVAL 6 HOUR)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Tournaments
        DB::table('tournaments')->update([
            'starts_at' => DB::raw('DATE_ADD(starts_at, INTERVAL 6 HOUR)'),
            'ends_at' => DB::raw('DATE_ADD(ends_at, INTERVAL 6 HOUR)'),
            'created_at' => DB::raw('DATE_ADD(created_at, INTERVAL 6 HOUR)'),
            'updated_at' => DB::raw('DATE_ADD(updated_at, INTERVAL 6 HOUR)'),
        ]);

        // 2. Matches
        DB::table('matches')->update([
            'starts_at' => DB::raw('DATE_ADD(starts_at, INTERVAL 6 HOUR)'),
            'predictions_locked_at' => DB::raw('DATE_ADD(predictions_locked_at, INTERVAL 6 HOUR)'),
            'result_locked_at' => DB::raw('DATE_ADD(result_locked_at, INTERVAL 6 HOUR)'),
            'result_entered_at' => DB::raw('DATE_ADD(result_entered_at, INTERVAL 6 HOUR)'),
            'created_at' => DB::raw('DATE_ADD(created_at, INTERVAL 6 HOUR)'),
            'updated_at' => DB::raw('DATE_ADD(updated_at, INTERVAL 6 HOUR)'),
        ]);

        // 3. Pools
        DB::table('pools')->update([
            'starts_at' => DB::raw('DATE_ADD(starts_at, INTERVAL 6 HOUR)'),
            'ends_at' => DB::raw('DATE_ADD(ends_at, INTERVAL 6 HOUR)'),
            'created_at' => DB::raw('DATE_ADD(created_at, INTERVAL 6 HOUR)'),
            'updated_at' => DB::raw('DATE_ADD(updated_at, INTERVAL 6 HOUR)'),
        ]);

        // 4. Pool Participants
        DB::table('pool_participants')->update([
            'joined_at' => DB::raw('DATE_ADD(joined_at, INTERVAL 6 HOUR)'),
            'approved_at' => DB::raw('DATE_ADD(approved_at, INTERVAL 6 HOUR)'),
        ]);

        // 5. Users
        DB::table('users')->update([
            'created_at' => DB::raw('DATE_ADD(created_at, INTERVAL 6 HOUR)'),
            'updated_at' => DB::raw('DATE_ADD(updated_at, INTERVAL 6 HOUR)'),
        ]);
    }
};
