<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Models\Team;
use App\Models\GameMatch;
use App\Models\ScoringRule;
use App\Models\Pool;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedWorldCup2026 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quinmariscal:seed-worldcup-2026';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carga el torneo Mundial FIFA 2026 con 12 grupos, 48 selecciones, reglas iniciales y 104 partidos.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando carga de la Quiniela Mundial FIFA 2026...');

        // 1. App Settings
        $this->info('Cargando configuraciones iniciales de QuinMariscal...');
        AppSetting::setValue('registration_enabled', true, 'boolean', 'general', 'Habilitar autoregistro de usuarios');
        AppSetting::setValue('registration_requires_approval', false, 'boolean', 'general', 'Requiere aprobación de administrador para registro');
        AppSetting::setValue('allowed_email_domains', '', 'string', 'general', 'Dominios de email permitidos (separados por coma, ej: distmariscal.com,mariscal.gt). Dejar vacío para registro abierto.');
        AppSetting::setValue('require_invitation_code', false, 'boolean', 'general', 'Requiere código de invitación para unirse a quinielas');
        AppSetting::setValue('public_rankings_enabled', true, 'boolean', 'general', 'Permitir visualización de rankings públicos');
        AppSetting::setValue('predictions_close_minutes_before_match', 0, 'integer', 'general', 'Minutos antes del inicio del partido para cerrar pronósticos');
        AppSetting::setValue('terms_and_conditions_text', 'Esta quiniela es una actividad recreativa interna de Distribuidora Mariscal. La participación no implica apuestas monetarias obligatorias. Las reglas, premios simbólicos y criterios de desempate serán definidos por la administración.', 'string', 'general', 'Texto de términos y condiciones');
        AppSetting::setValue('welcome_message', '¡Bienvenido a QuinMariscal! La quiniela mundialista oficial de Distribuidora Mariscal.', 'string', 'general', 'Mensaje de bienvenida');

        // 2. Tournament
        $this->info('Creando Torneo...');
        $tournament = Tournament::updateOrCreate(
            ['slug' => 'mundial-fifa-2026'],
            [
                'name' => 'Mundial FIFA 2026',
                'description' => 'Quiniela oficial de Distribuidora Mariscal para el Mundial de Fútbol FIFA 2026 (EE.UU., Canadá y México).',
                'year' => 2026,
                'status' => 'active',
                'starts_at' => '2026-06-11 12:00:00',
                'ends_at' => '2026-07-19 18:00:00',
                'is_active' => true,
            ]
        );

        // 3. Scoring Rules
        $this->info('Creando Reglas de Puntuación...');
        $rules = [
            ['key' => 'exact_score', 'name' => 'Marcador Exacto', 'points' => 3, 'description' => 'Acertar los goles exactos del local y del visitante.'],
            ['key' => 'correct_winner', 'name' => 'Ganador Correcto', 'points' => 1, 'description' => 'Acertar el equipo ganador sin acertar el marcador exacto.'],
            ['key' => 'correct_draw', 'name' => 'Empate Correcto', 'points' => 1, 'description' => 'Acertar que el partido termina en empate sin acertar el marcador exacto.'],
            ['key' => 'knockout_winner', 'name' => 'Ganador de Eliminación', 'points' => 1, 'description' => 'Acertar el equipo que pasa de ronda en fases eliminatorias (tiempo extra/penales).'],
        ];

        foreach ($rules as $r) {
            ScoringRule::updateOrCreate(
                [
                    'tournament_id' => $tournament->id,
                    'key' => $r['key']
                ],
                [
                    'name' => $r['name'],
                    'points' => $r['points'],
                    'description' => $r['description'],
                    'is_active' => true,
                ]
            );
        }

        // 4. Create an Admin user if none exists
        $admin = User::where('role', 'super_admin')->first();
        if (!$admin) {
            $admin = User::updateOrCreate(
                ['email' => 'admin@quinmariscal.local'],
                [
                    'name' => 'Administrador QuinMariscal',
                    'password' => bcrypt('password'),
                    'role' => 'super_admin',
                    'status' => 'active',
                    'phone' => '12345678',
                    'employee_code' => 'ADMIN01',
                    'department' => 'Administración',
                    'branch' => 'Central',
                    'company' => 'Distribuidora Mariscal',
                    'accepted_terms' => true,
                ]
            );
            $this->info('Usuario Administrador inicial creado (admin@quinmariscal.local / password).');
        }

        // 5. Default Pool
        $this->info('Creando la Quiniela General...');
        $pool = Pool::updateOrCreate(
            ['slug' => 'quiniela-general-mariscal'],
            [
                'tournament_id' => $tournament->id,
                'name' => 'Quiniela General Mariscal',
                'description' => 'La quiniela general abierta para todos los colaboradores de Distribuidora Mariscal.',
                'visibility' => 'public',
                'join_mode' => 'open',
                'is_active' => true,
                'created_by' => $admin->id,
                'starts_at' => '2026-06-11 12:00:00',
                'ends_at' => '2026-07-19 18:00:00',
            ]
        );

        // 6. Groups and Teams (48 teams distributed in 12 groups A to L)
        $this->info('Creando Grupos y Selecciones del Mundial...');
        $groupsData = [
            'A' => [
                ['name' => 'México', 'fifa' => 'MEX', 'cc' => 'mx'],
                ['name' => 'Estados Unidos', 'fifa' => 'USA', 'cc' => 'us'],
                ['name' => 'Canadá', 'fifa' => 'CAN', 'cc' => 'ca'],
                ['name' => 'Costa Rica', 'fifa' => 'CRC', 'cc' => 'cr'],
            ],
            'B' => [
                ['name' => 'Argentina', 'fifa' => 'ARG', 'cc' => 'ar'],
                ['name' => 'Ecuador', 'fifa' => 'ECU', 'cc' => 'ec'],
                ['name' => 'Chile', 'fifa' => 'CHI', 'cc' => 'cl'],
                ['name' => 'Venezuela', 'fifa' => 'VEN', 'cc' => 've'],
            ],
            'C' => [
                ['name' => 'Brasil', 'fifa' => 'BRA', 'cc' => 'br'],
                ['name' => 'Colombia', 'fifa' => 'COL', 'cc' => 'co'],
                ['name' => 'Uruguay', 'fifa' => 'URU', 'cc' => 'uy'],
                ['name' => 'Paraguay', 'fifa' => 'PAR', 'cc' => 'py'],
            ],
            'D' => [
                ['name' => 'España', 'fifa' => 'ESP', 'cc' => 'es'],
                ['name' => 'Portugal', 'fifa' => 'POR', 'cc' => 'pt'],
                ['name' => 'Marruecos', 'fifa' => 'MAR', 'cc' => 'ma'],
                ['name' => 'Egipto', 'fifa' => 'EGY', 'cc' => 'eg'],
            ],
            'E' => [
                ['name' => 'Francia', 'fifa' => 'FRA', 'cc' => 'fr'],
                ['name' => 'Países Bajos', 'fifa' => 'NED', 'cc' => 'nl'],
                ['name' => 'Senegal', 'fifa' => 'SEN', 'cc' => 'sn'],
                ['name' => 'Mali', 'fifa' => 'MLI', 'cc' => 'ml'],
            ],
            'F' => [
                ['name' => 'Inglaterra', 'fifa' => 'ENG', 'cc' => 'gb'],
                ['name' => 'Gales', 'fifa' => 'WAL', 'cc' => 'gb'],
                ['name' => 'Escocia', 'fifa' => 'SCO', 'cc' => 'gb'],
                ['name' => 'Jamaica', 'fifa' => 'JAM', 'cc' => 'jm'],
            ],
            'G' => [
                ['name' => 'Alemania', 'fifa' => 'GER', 'cc' => 'de'],
                ['name' => 'Italia', 'fifa' => 'ITA', 'cc' => 'it'],
                ['name' => 'Suiza', 'fifa' => 'SUI', 'cc' => 'ch'],
                ['name' => 'Camerún', 'fifa' => 'CMR', 'cc' => 'cm'],
            ],
            'H' => [
                ['name' => 'Bélgica', 'fifa' => 'BEL', 'cc' => 'be'],
                ['name' => 'Croacia', 'fifa' => 'CRO', 'cc' => 'hr'],
                ['name' => 'Túnez', 'fifa' => 'TUN', 'cc' => 'tn'],
                ['name' => 'Nigeria', 'fifa' => 'NGA', 'cc' => 'ng'],
            ],
            'I' => [
                ['name' => 'Dinamarca', 'fifa' => 'DEN', 'cc' => 'dk'],
                ['name' => 'Suecia', 'fifa' => 'SWE', 'cc' => 'se'],
                ['name' => 'Polonia', 'fifa' => 'POL', 'cc' => 'pl'],
                ['name' => 'Ghana', 'fifa' => 'GHA', 'cc' => 'gh'],
            ],
            'J' => [
                ['name' => 'Japón', 'fifa' => 'JPN', 'cc' => 'jp'],
                ['name' => 'Corea del Sur', 'fifa' => 'KOR', 'cc' => 'kr'],
                ['name' => 'Australia', 'fifa' => 'AUS', 'cc' => 'au'],
                ['name' => 'Arabia Saudita', 'fifa' => 'KSA', 'cc' => 'sa'],
            ],
            'K' => [
                ['name' => 'Irán', 'fifa' => 'IRN', 'cc' => 'ir'],
                ['name' => 'Irak', 'fifa' => 'IRQ', 'cc' => 'iq'],
                ['name' => 'Qatar', 'fifa' => 'QAT', 'cc' => 'qa'],
                ['name' => 'Emiratos Árabes', 'fifa' => 'UAE', 'cc' => 'ae'],
            ],
            'L' => [
                ['name' => 'Perú', 'fifa' => 'PER', 'cc' => 'pe'],
                ['name' => 'Bolivia', 'fifa' => 'BOL', 'cc' => 'bo'],
                ['name' => 'Honduras', 'fifa' => 'HON', 'cc' => 'hn'],
                ['name' => 'Panamá', 'fifa' => 'PAN', 'cc' => 'pa'],
            ],
        ];

        $groups = [];
        $teams = [];

        $gOrder = 1;
        foreach ($groupsData as $gCode => $teamsInGroup) {
            $group = TournamentGroup::updateOrCreate(
                [
                    'tournament_id' => $tournament->id,
                    'code' => $gCode
                ],
                [
                    'name' => "Grupo {$gCode}",
                    'order' => $gOrder++,
                ]
            );
            $groups[$gCode] = $group;

            foreach ($teamsInGroup as $tData) {
                $team = Team::updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'fifa_code' => $tData['fifa']
                    ],
                    [
                        'tournament_group_id' => $group->id,
                        'name' => $tData['name'],
                        'official_name' => "Selección de " . $tData['name'],
                        'country_code' => strtoupper($tData['cc']),
                        'flag_url' => "https://flagcdn.com/w160/" . strtolower($tData['cc']) . ".png",
                        'is_active' => true,
                    ]
                );
                $teams[$tData['fifa']] = $team;
            }
        }

        // 7. Matches (104 matches total: 72 Group stage, 16 round of 32, 8 round of 16, 4 quarter final, 2 semi final, 1 third place, 1 final)
        $this->info('Programando Partidos (Calendario de 104 partidos)...');

        $matchNumber = 1;
        $baseDate = now()->addDays(5)->setTime(12, 0, 0); // Start matches in 5 days for local testing

        // A. Group Stage (72 Matches: 6 matches per group * 12 groups)
        foreach ($groupsData as $gCode => $teamsInGroup) {
            $group = $groups[$gCode];
            $fifaCodes = array_column($teamsInGroup, 'fifa');

            // Standard round robin matchups for 4 teams:
            // Match 1: 1 vs 2
            // Match 2: 3 vs 4
            // Match 3: 1 vs 3
            // Match 4: 2 vs 4
            // Match 5: 1 vs 4
            // Match 6: 2 vs 3
            $matchups = [
                [$fifaCodes[0], $fifaCodes[1]],
                [$fifaCodes[2], $fifaCodes[3]],
                [$fifaCodes[0], $fifaCodes[3]],
                [$fifaCodes[1], $fifaCodes[2]],
                [$fifaCodes[0], $fifaCodes[2]],
                [$fifaCodes[1], $fifaCodes[3]],
            ];

            foreach ($matchups as $index => $pair) {
                $home = $teams[$pair[0]];
                $away = $teams[$pair[1]];

                // Shift dates slightly to make calendar look staggered
                $matchTime = $baseDate->copy()->addHours($matchNumber * 3);

                GameMatch::updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'match_number' => $matchNumber
                    ],
                    [
                        'tournament_group_id' => $group->id,
                        'home_team_id' => $home->id,
                        'away_team_id' => $away->id,
                        'stage' => 'group_stage',
                        'starts_at' => $matchTime->toDateTimeString(),
                        'status' => 'scheduled',
                        'stadium' => 'Estadio Mundialista',
                        'city' => 'Ciudad Sede',
                        'country' => 'Norteamérica 2026',
                    ]
                );

                $matchNumber++;
            }
        }

        // B. Elimination Stages with Placeholders (32 Matches remaining)
        $stages = [
            'round_of_32' => 16,
            'round_of_16' => 8,
            'quarter_final' => 4,
            'semi_final' => 2,
            'third_place' => 1,
            'final' => 1,
        ];

        foreach ($stages as $stageKey => $qty) {
            for ($i = 1; $i <= $qty; $i++) {
                $matchTime = $baseDate->copy()->addDays(15)->addHours($matchNumber * 4);

                $homePlaceholder = "";
                $awayPlaceholder = "";

                switch ($stageKey) {
                    case 'round_of_32':
                        $homePlaceholder = "1º Grupo " . chr(64 + ceil($i / 2));
                        $awayPlaceholder = "Mejor 3º / 2º Grupo " . chr(65 + ($i % 12));
                        break;
                    case 'round_of_16':
                        $homePlaceholder = "Ganador Partido " . ($matchNumber - 24);
                        $awayPlaceholder = "Ganador Partido " . ($matchNumber - 23);
                        break;
                    case 'quarter_final':
                        $homePlaceholder = "Ganador Partido " . ($matchNumber - 12);
                        $awayPlaceholder = "Ganador Partido " . ($matchNumber - 11);
                        break;
                    case 'semi_final':
                        $homePlaceholder = "Ganador Partido " . ($matchNumber - 6);
                        $awayPlaceholder = "Ganador Partido " . ($matchNumber - 5);
                        break;
                    case 'third_place':
                        $homePlaceholder = "Perdedor Semifinal 1";
                        $awayPlaceholder = "Perdedor Semifinal 2";
                        break;
                    case 'final':
                        $homePlaceholder = "Ganador Semifinal 1";
                        $awayPlaceholder = "Ganador Semifinal 2";
                        break;
                }

                GameMatch::updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'match_number' => $matchNumber
                    ],
                    [
                        'tournament_group_id' => null,
                        'home_team_id' => null,
                        'away_team_id' => null,
                        'home_placeholder' => $homePlaceholder,
                        'away_placeholder' => $awayPlaceholder,
                        'stage' => $stageKey,
                        'starts_at' => $matchTime->toDateTimeString(),
                        'status' => 'scheduled',
                        'stadium' => 'Estadio de Eliminación',
                        'city' => 'Sede Eliminatoria',
                        'country' => 'Norteamérica 2026',
                    ]
                );

                $matchNumber++;
            }
        }

        $this->info("¡Carga exitosa! Se cargó el torneo Mundial FIFA 2026.");
        $this->info("- Selecciones cargadas: 48");
        $this->info("- Grupos cargados: 12");
        $this->info("- Reglas de puntuación creadas: 4");
        $this->info("- Total partidos creados: 104");
        $this->info("- Quiniela General creada: 'Quiniela General Mariscal'");

        return Command::SUCCESS;
    }
}
