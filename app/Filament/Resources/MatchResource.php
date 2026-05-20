<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MatchResource\Pages;
use App\Models\GameMatch;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Services\Quiniela\PredictionScoringService;
use App\Services\Quiniela\RankingService;
use App\Models\Pool;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class MatchResource extends Resource
{
    protected static ?string $model = GameMatch::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar';

    protected static \UnitEnum|string|null $navigationGroup = 'Datos Deportivos';

    protected static ?string $modelLabel = 'Partido';

    protected static ?string $pluralModelLabel = 'Partidos';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('tournament_id')
                            ->label('Torneo')
                            ->relationship('tournament', 'name')
                            ->required(),
                        Forms\Components\Select::make('tournament_group_id')
                            ->label('Grupo (Opcional)')
                            ->relationship('group', 'name')
                            ->nullable(),
                        Forms\Components\Select::make('home_team_id')
                            ->label('Equipo Local')
                            ->options(fn () => Team::pluck('name', 'id'))
                            ->nullable()
                            ->searchable(),
                        Forms\Components\Select::make('away_team_id')
                            ->label('Equipo Visitante')
                            ->options(fn () => Team::pluck('name', 'id'))
                            ->nullable()
                            ->searchable(),
                        Forms\Components\TextInput::make('home_placeholder')
                            ->label('Placeholder Local (ej. Ganador A)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('away_placeholder')
                            ->label('Placeholder Visitante (ej. Segundo B)')
                            ->maxLength(255),
                        Forms\Components\Select::make('stage')
                            ->label('Fase')
                            ->options([
                                'group_stage' => 'Fase de Grupos',
                                'round_of_32' => 'Dieciseisavos (Ronda de 32)',
                                'round_of_16' => 'Octavos de Final',
                                'quarter_final' => 'Cuartos de Final',
                                'semi_final' => 'Semifinales',
                                'third_place' => 'Tercer Lugar',
                                'final' => 'Final',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('match_number')
                            ->label('Número de Partido')
                            ->required()
                            ->numeric(),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Fecha/Hora de Inicio')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'scheduled' => 'Programado',
                                'live' => 'En Vivo',
                                'finished' => 'Terminado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required()
                            ->default('scheduled'),
                        Forms\Components\TextInput::make('stadium')
                            ->label('Estadio'),
                        Forms\Components\TextInput::make('city')
                            ->label('Ciudad'),
                        Forms\Components\TextInput::make('country')
                            ->label('País'),
                        Forms\Components\DateTimePicker::make('predictions_locked_at')
                            ->label('Bloqueo Manual de Pronósticos (Opcional)'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('Resultados Reales')
                            ->content('Ingrese resultados solo si el partido finalizó.'),
                        Forms\Components\TextInput::make('home_score')
                            ->label('Goles Local')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('away_score')
                            ->label('Goles Visitante')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('home_penalty_score')
                            ->label('Penales Local (Opcional)')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('away_penalty_score')
                            ->label('Penales Visitante (Opcional)')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\Select::make('winner_team_id')
                            ->label('Ganador Oficial (Eliminatoria/Penales)')
                            ->options(fn ($get) => Team::whereIn('id', [
                                $get('home_team_id'),
                                $get('away_team_id')
                            ])->pluck('name', 'id'))
                            ->nullable(),
                    ])->columns(3)->visible(fn ($get) => $get('status') === 'finished')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('match_number')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),
                Tables\Columns\TextColumn::make('stage')
                    ->label('Fase')
                    ->formatStateUsing(fn ($state) => [
                        'group_stage' => 'Grupo',
                        'round_of_32' => 'Ronda 32',
                        'round_of_16' => 'Octavos',
                        'quarter_final' => 'Cuartos',
                        'semi_final' => 'Semis',
                        'third_place' => '3er Lugar',
                        'final' => 'Final',
                    ][$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('teams')
                    ->label('Encuentro')
                    ->formatStateUsing(function (GameMatch $record) {
                        $home = $record->homeTeam ? $record->homeTeam->name : ($record->home_placeholder ?: 'TBD');
                        $away = $record->awayTeam ? $record->awayTeam->name : ($record->away_placeholder ?: 'TBD');
                        
                        if ($record->status === 'finished') {
                            $res = " {$record->home_score} - {$record->away_score} ";
                            if ($record->home_penalty_score !== null || $record->away_penalty_score !== null) {
                                $res .= " ({$record->home_penalty_score} - {$record->away_penalty_score} Pen) ";
                            }
                            return "{$home} {$res} {$away}";
                        }
                        
                        return "{$home} vs {$away}";
                    }),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Hora de Inicio')
                    ->dateTime('d/m H:i')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'scheduled',
                        'primary' => 'live',
                        'success' => 'finished',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => [
                        'scheduled' => 'Programado',
                        'live' => 'En Vivo',
                        'finished' => 'Terminado',
                        'cancelled' => 'Cancelado',
                    ][$state] ?? $state),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage')
                    ->label('Fase')
                    ->options([
                        'group_stage' => 'Fase de Grupos',
                        'round_of_32' => 'Dieciseisavos',
                        'round_of_16' => 'Octavos de Final',
                        'quarter_final' => 'Cuartos de Final',
                        'semi_final' => 'Semifinales',
                        'third_place' => 'Tercer Lugar',
                        'final' => 'Final',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'scheduled' => 'Programado',
                        'live' => 'En Vivo',
                        'finished' => 'Terminado',
                        'cancelled' => 'Cancelado',
                    ]),
            ])
            ->actions([
                // Custom Action: Enter Match Result and Recalculate in one click!
                \Filament\Actions\Action::make('enter_result')
                    ->label('Cargar Resultado')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (GameMatch $record) => $record->status !== 'finished')
                    ->form([
                        Forms\Components\TextInput::make('home_score')
                            ->label(fn (GameMatch $record) => 'Goles de ' . ($record->homeTeam ? $record->homeTeam->name : ($record->home_placeholder ?: 'Local')))
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('away_score')
                            ->label(fn (GameMatch $record) => 'Goles de ' . ($record->awayTeam ? $record->awayTeam->name : ($record->away_placeholder ?: 'Visitante')))
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        \Filament\Schemas\Components\Section::make('Fase Eliminatoria (Desempate)')
                            ->description('Solo si aplica tanda de penales en rondas de eliminación.')
                            ->schema([
                                Forms\Components\TextInput::make('home_penalty_score')
                                    ->label('Penales Local')
                                    ->numeric()
                                    ->minValue(0),
                                Forms\Components\TextInput::make('away_penalty_score')
                                    ->label('Penales Visitante')
                                    ->numeric()
                                    ->minValue(0),
                                Forms\Components\Select::make('winner_team_id')
                                    ->label('Ganador que Avanza')
                                    ->options(fn (GameMatch $record) => Team::whereIn('id', [
                                        $record->home_team_id,
                                        $record->away_team_id
                                    ])->pluck('name', 'id'))
                                    ->nullable(),
                            ])->visible(fn (GameMatch $record) => $record->stage !== 'group_stage'),
                    ])
                    ->action(function (GameMatch $record, array $data, PredictionScoringService $scoringService, RankingService $rankingService): void {
                        $record->update([
                            'home_score' => $data['home_score'],
                            'away_score' => $data['away_score'],
                            'home_penalty_score' => $data['home_penalty_score'] ?? null,
                            'away_penalty_score' => $data['away_penalty_score'] ?? null,
                            'winner_team_id' => $data['winner_team_id'] ?? null,
                            'status' => 'finished',
                            'result_entered_by' => auth()->id(),
                            'result_entered_at' => now(),
                            'result_locked_at' => now(),
                        ]);

                        // Recalculate predictions
                        $count = $scoringService->scoreMatchPredictions($record->id);

                        // Recalculate pool rankings
                        $pools = Pool::where('tournament_id', $record->tournament_id)->get();
                        foreach ($pools as $pool) {
                            $rankingService->recalculatePoolRankings($pool->id);
                            $rankingService->captureSnapshot($pool->id);
                        }

                        Notification::make()
                            ->title('Resultado Registrado')
                            ->body("Se ingresó el marcador y se recalcularon {$count} pronósticos con sus respectivos rankings.")
                            ->success()
                            ->send();
                    }),

                \Filament\Actions\Action::make('recalculate')
                    ->label('Recalcular')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (GameMatch $record) => $record->status === 'finished')
                    ->action(function (GameMatch $record, PredictionScoringService $scoringService, RankingService $rankingService): void {
                        $count = $scoringService->scoreMatchPredictions($record->id);
                        
                        $pools = Pool::where('tournament_id', $record->tournament_id)->get();
                        foreach ($pools as $pool) {
                            $rankingService->recalculatePoolRankings($pool->id);
                            $rankingService->captureSnapshot($pool->id);
                        }

                        Notification::make()
                            ->title('Recálculo Completado')
                            ->body("Se actualizaron {$count} pronósticos y rankings.")
                            ->info()
                            ->send();
                    }),

                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMatches::route('/'),
            'create' => Pages\CreateMatch::route('/create'),
            'edit' => Pages\EditMatch::route('/{record}/edit'),
        ];
    }
}
