<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tournaments
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('year');
            $table->string('status')->default('draft'); // draft, active, finished
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tournament Groups
        Schema::create('tournament_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->string('name');
            $table->string('code'); // A, B, C, ..., L
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 3. Teams (Selecciones)
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->nullable()->constrained('tournaments')->nullOnDelete();
            $table->foreignId('tournament_group_id')->nullable()->constrained('tournament_groups')->nullOnDelete();
            $table->string('name');
            $table->string('official_name')->nullable();
            $table->string('fifa_code')->index();
            $table->string('country_code')->nullable(); // GT, US, MX, etc.
            $table->string('flag_url')->nullable();
            $table->string('flag_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Matches
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignId('tournament_group_id')->nullable()->constrained('tournament_groups')->nullOnDelete();
            $table->foreignId('home_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('away_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('home_placeholder')->nullable(); // e.g. "Ganador Grupo A"
            $table->string('away_placeholder')->nullable();
            $table->string('stage'); // group_stage, round_of_32, round_of_16, quarter_final, semi_final, third_place, final
            $table->integer('match_number')->index();
            $table->string('stadium')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->dateTime('starts_at')->index();
            $table->string('status')->default('scheduled'); // scheduled, live, finished, cancelled
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->integer('home_penalty_score')->nullable();
            $table->integer('away_penalty_score')->nullable();
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->dateTime('predictions_locked_at')->nullable();
            $table->dateTime('result_locked_at')->nullable();
            $table->foreignId('result_entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('result_entered_at')->nullable();
            $table->timestamps();
        });

        // 5. Pools (Quinielas)
        Schema::create('pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('visibility')->default('public'); // public, private
            $table->string('join_mode')->default('open'); // open, approval_required, invitation_code
            $table->string('invitation_code')->nullable();
            $table->integer('max_participants')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 6. Pool Participants
        Schema::create('pool_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->constrained('pools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('approved'); // pending, approved, rejected, blocked
            $table->dateTime('joined_at');
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('total_points')->default(0)->index();
            $table->integer('exact_scores_count')->default(0);
            $table->integer('correct_results_count')->default(0);
            $table->integer('failed_predictions_count')->default(0);
            $table->integer('current_rank')->nullable();
            $table->integer('previous_rank')->nullable();
            $table->timestamps();

            $table->unique(['pool_id', 'user_id']);
        });

        // 7. Predictions
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->constrained('pools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->integer('predicted_home_score');
            $table->integer('predicted_away_score');
            $table->integer('predicted_home_penalty_score')->nullable();
            $table->integer('predicted_away_penalty_score')->nullable();
            $table->foreignId('predicted_winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->integer('points_awarded')->default(0);
            $table->json('scoring_detail')->nullable();
            $table->dateTime('submitted_at');
            $table->dateTime('locked_at')->nullable();
            $table->dateTime('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['pool_id', 'user_id', 'match_id']);
        });

        // 8. Scoring Rules
        Schema::create('scoring_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->string('name');
            $table->string('key'); // exact_score, correct_winner, correct_draw, etc.
            $table->integer('points');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 9. Ranking Snapshots
        Schema::create('ranking_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->constrained('pools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('total_points');
            $table->integer('rank');
            $table->date('snapshot_date');
            $table->timestamps();
        });

        // 10. App Settings
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, array
            $table->string('group')->default('general');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 11. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('ranking_snapshots');
        Schema::dropIfExists('scoring_rules');
        Schema::dropIfExists('predictions');
        Schema::dropIfExists('pool_participants');
        Schema::dropIfExists('pools');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('tournament_groups');
        Schema::dropIfExists('tournaments');
    }
};
