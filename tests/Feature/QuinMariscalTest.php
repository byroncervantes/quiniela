<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Models\Team;
use App\Models\GameMatch;
use App\Models\Pool;
use App\Models\PoolParticipant;
use App\Models\Prediction;
use App\Models\ScoringRule;
use App\Models\AppSetting;
use App\Services\Quiniela\PredictionScoringService;
use App\Services\Quiniela\RankingService;
use App\Services\Quiniela\MatchLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuinMariscalTest extends TestCase
{
    use RefreshDatabase;

    protected $tournament;
    protected $pool;
    protected $teamA;
    protected $teamB;
    protected $match;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Tournament
        $this->tournament = Tournament::create([
            'name' => 'Mundial FIFA 2026',
            'slug' => 'mundial-fifa-2026',
            'year' => 2026,
            'status' => 'active',
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(35),
            'is_active' => true,
        ]);

        // 2. Create Scoring Rules
        ScoringRule::create([
            'tournament_id' => $this->tournament->id,
            'name' => 'Marcador Exacto',
            'key' => 'exact_score',
            'points' => 3,
            'is_active' => true,
        ]);

        ScoringRule::create([
            'tournament_id' => $this->tournament->id,
            'name' => 'Ganador Correcto',
            'key' => 'correct_winner',
            'points' => 1,
            'is_active' => true,
        ]);

        ScoringRule::create([
            'tournament_id' => $this->tournament->id,
            'name' => 'Empate Correcto',
            'key' => 'correct_draw',
            'points' => 1,
            'is_active' => true,
        ]);

        $dept = \App\Models\Department::firstOrCreate([
            'name' => 'Administración',
        ], [
            'is_active' => true,
        ]);

        // 3. Create Admin User
        $this->admin = User::create([
            'name' => 'Administrador QuinMariscal',
            'email' => 'admin@quinmariscal.local',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status' => 'active',
            'employee_code' => '1000000000001',
            'department_id' => $dept->id,
            'company' => 'Distribuidora Mariscal',
            'accepted_terms' => true,
        ]);

        // 4. Create Pool
        $this->pool = Pool::create([
            'tournament_id' => $this->tournament->id,
            'name' => 'Quiniela General Mariscal',
            'slug' => 'quiniela-general-mariscal',
            'visibility' => 'public',
            'join_mode' => 'open',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        // 5. Create Group & Teams
        $group = TournamentGroup::create([
            'tournament_id' => $this->tournament->id,
            'name' => 'Grupo A',
            'code' => 'A',
        ]);

        $this->teamA = Team::create([
            'tournament_id' => $this->tournament->id,
            'tournament_group_id' => $group->id,
            'name' => 'Guatemala',
            'fifa_code' => 'GUA',
            'is_active' => true,
        ]);

        $this->teamB = Team::create([
            'tournament_id' => $this->tournament->id,
            'tournament_group_id' => $group->id,
            'name' => 'Argentina',
            'fifa_code' => 'ARG',
            'is_active' => true,
        ]);

        // 6. Create Match
        $this->match = GameMatch::create([
            'tournament_id' => $this->tournament->id,
            'tournament_group_id' => $group->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'stage' => 'group_stage',
            'match_number' => 1,
            'starts_at' => now()->addDays(5),
            'status' => 'scheduled',
        ]);
    }

    /**
     * Test User Autoregistro and Domain Validation
     */
    public function test_user_can_register_and_validates_domain(): void
    {
        // Enable domain restriction settings in DB
        AppSetting::setValue('registration_enabled', true, 'boolean');
        AppSetting::setValue('allowed_email_domains', 'distmariscal.com,mariscal.gt', 'string');
        AppSetting::setValue('registration_requires_approval', false, 'boolean');

        // Create a test branch
        $branch = \App\Models\Branch::firstOrCreate([
            'name' => 'Sucursal Villa Nueva'
        ], [
            'is_active' => true,
        ]);

        // Create a test department
        $department = \App\Models\Department::firstOrCreate([
            'name' => 'Administración'
        ], [
            'is_active' => true,
        ]);

        // 1. Attempt invalid domain registration
        $response = $this->post('/register', [
            'name' => 'Colaborador Invalido',
            'email' => 'invalido@gmail.com',
            'phone' => '12345678',
            'employee_code' => '1000000000002',
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
            'accepted_terms' => 'on',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'invalido@gmail.com']);

        // 2. Attempt valid domain registration
        $response2 = $this->post('/register', [
            'name' => 'Colaborador Valido',
            'email' => 'colaborador@distmariscal.com',
            'phone' => '87654321',
            'employee_code' => '1000000000003',
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
            'accepted_terms' => 'on',
        ]);

        $response2->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'colaborador@distmariscal.com', 'status' => 'active']);
    }

    /**
     * Test Prediction limits and locks based on time
     */
    public function test_prediction_time_locking(): void
    {
        // Create user and approve them in the pool
        $user = User::create([
            'name' => 'Jugador Pruebas',
            'email' => 'tester@distmariscal.com',
            'password' => Hash::make('password'),
            'role' => 'participante',
            'status' => 'active',
            'employee_code' => '1000000000004',
        ]);

        PoolParticipant::create([
            'pool_id' => $this->pool->id,
            'user_id' => $user->id,
            'status' => 'approved',
            'joined_at' => now(),
        ]);

        // Login
        $this->actingAs($user);

        // 1. Predict a match starting in 5 days (should succeed)
        $response = $this->postJson(route('pools.predictions.save-ajax', $this->pool->slug), [
            'match_id' => $this->match->id,
            'home_score' => 2,
            'away_score' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('predictions', [
            'pool_id' => $this->pool->id,
            'user_id' => $user->id,
            'match_id' => $this->match->id,
            'predicted_home_score' => 2,
            'predicted_away_score' => 1,
        ]);

        // 2. Lock the match manually by setting starts_at to the past
        $this->match->update([
            'starts_at' => now()->subMinutes(10),
        ]);

        // 3. Attempt to predict again (should fail)
        $response2 = $this->postJson(route('pools.predictions.save-ajax', $this->pool->slug), [
            'match_id' => $this->match->id,
            'home_score' => 3,
            'away_score' => 0,
        ]);

        $response2->assertStatus(400); // Bad Request due to lock
    }

    /**
     * Test Prediction Point Scoring Calculation
     */
    public function test_prediction_points_scoring_calculations(): void
    {
        $scoringService = new PredictionScoringService();

        // Create separate users to avoid unique constraint violations (pool_id, user_id, match_id)
        $userB = User::create([
            'name' => 'User B',
            'email' => 'userb@distmariscal.com',
            'password' => Hash::make('password'),
            'employee_code' => '1000000000005',
        ]);
        $userC = User::create([
            'name' => 'User C',
            'email' => 'userc@distmariscal.com',
            'password' => Hash::make('password'),
            'employee_code' => '1000000000006',
        ]);

        // 1. Prediction A: Exact Score (Predicted 2-1, Match was 2-1)
        $predA = Prediction::create([
            'pool_id' => $this->pool->id,
            'user_id' => $this->admin->id, // Use admin
            'match_id' => $this->match->id,
            'predicted_home_score' => 2,
            'predicted_away_score' => 1,
            'submitted_at' => now(),
        ]);

        // 2. Prediction B: Correct Winner (Predicted 3-0, Match was 2-1)
        $predB = Prediction::create([
            'pool_id' => $this->pool->id,
            'user_id' => $userB->id, // Use user B
            'match_id' => $this->match->id,
            'predicted_home_score' => 3,
            'predicted_away_score' => 0,
            'submitted_at' => now(),
        ]);

        // 3. Prediction C: Incorrect outcome (Predicted 1-2, Match was 2-1)
        $predC = Prediction::create([
            'pool_id' => $this->pool->id,
            'user_id' => $userC->id, // Use user C
            'match_id' => $this->match->id,
            'predicted_home_score' => 1,
            'predicted_away_score' => 2,
            'submitted_at' => now(),
        ]);

        // Finish the match with score 2 - 1
        $this->match->update([
            'home_score' => 2,
            'away_score' => 1,
            'status' => 'finished',
        ]);

        // Calculate points
        $scoreA = $scoringService->scorePrediction($predA, $this->match);
        $scoreB = $scoringService->scorePrediction($predB, $this->match);
        $scoreC = $scoringService->scorePrediction($predC, $this->match);

        $this->assertEquals(3, $scoreA['points']); // Exact Score = 3 pts
        $this->assertTrue($scoreA['scoring_detail']['exact_score']);

        $this->assertEquals(1, $scoreB['points']); // Correct Winner = 1 pt
        $this->assertTrue($scoreB['scoring_detail']['correct_winner']);

        $this->assertEquals(0, $scoreC['points']); // Incorrect = 0 pts
        $this->assertFalse($scoreC['scoring_detail']['exact_score']);
        $this->assertFalse($scoreC['scoring_detail']['correct_winner']);
    }

    /**
     * Test Ranking Recalculation and Snapshot Tiebreakers
     */
    public function test_ranking_recalculation_and_snapshots(): void
    {
        $rankingService = new RankingService();

        // Create 2 users
        $user1 = User::create(['name' => 'User One', 'email' => 'one@distmariscal.com', 'password' => Hash::make('pass'), 'employee_code' => '1000000000007']);
        $user2 = User::create(['name' => 'User Two', 'email' => 'two@distmariscal.com', 'password' => Hash::make('pass'), 'employee_code' => '1000000000008']);

        $part1 = PoolParticipant::create(['pool_id' => $this->pool->id, 'user_id' => $user1->id, 'status' => 'approved', 'joined_at' => now()]);
        $part2 = PoolParticipant::create(['pool_id' => $this->pool->id, 'user_id' => $user2->id, 'status' => 'approved', 'joined_at' => now()->addHour()]);

        // Mock predictions for points
        Prediction::create([
            'pool_id' => $this->pool->id,
            'user_id' => $user1->id,
            'match_id' => $this->match->id,
            'predicted_home_score' => 2,
            'predicted_away_score' => 1,
            'points_awarded' => 3, // Exact hit!
            'scoring_detail' => ['exact_score' => true, 'correct_winner' => false, 'correct_draw' => false],
            'submitted_at' => now(),
            'calculated_at' => now(),
        ]);

        Prediction::create([
            'pool_id' => $this->pool->id,
            'user_id' => $user2->id,
            'match_id' => $this->match->id,
            'predicted_home_score' => 1,
            'predicted_away_score' => 0,
            'points_awarded' => 1, // Winner only!
            'scoring_detail' => ['exact_score' => false, 'correct_winner' => true, 'correct_draw' => false],
            'submitted_at' => now(),
            'calculated_at' => now(),
        ]);

        // Run rankings recalculation
        $rankingService->recalculatePoolRankings($this->pool->id);

        $part1->refresh();
        $part2->refresh();

        // User 1 must be Rank 1 (3 points vs 1 point)
        $this->assertEquals(1, $part1->current_rank);
        $this->assertEquals(3, $part1->total_points);

        $this->assertEquals(2, $part2->current_rank);
        $this->assertEquals(1, $part2->total_points);

        // Capture snapshot
        $snapshotsCount = $rankingService->captureSnapshot($this->pool->id);
        $this->assertEquals(2, $snapshotsCount);
        $this->assertDatabaseHas('ranking_snapshots', [
            'pool_id' => $this->pool->id,
            'user_id' => $user1->id,
            'rank' => 1,
        ]);
    }
}
