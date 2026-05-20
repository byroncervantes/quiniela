<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoolParticipantResource\Pages;
use App\Models\PoolParticipant;
use App\Models\Pool;
use App\Models\User;
use App\Services\Quiniela\RankingService;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class PoolParticipantResource extends Resource
{
    protected static ?string $model = PoolParticipant::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-check-badge';

    protected static \UnitEnum|string|null $navigationGroup = 'Quiniela';

    protected static ?string $modelLabel = 'Participante';

    protected static ?string $pluralModelLabel = 'Participantes';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('pool_id')
                            ->label('Quiniela')
                            ->relationship('pool', 'name')
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario Colaborador')
                            ->relationship('user', 'name')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Estado de Participación')
                            ->options([
                                'pending' => 'Pendiente',
                                'approved' => 'Aprobado',
                                'rejected' => 'Rechazado',
                                'blocked' => 'Bloqueado',
                            ])
                            ->required()
                            ->default('approved'),
                        Forms\Components\DateTimePicker::make('joined_at')
                            ->label('Fecha de Unión')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('total_points')
                            ->label('Puntos Totales')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('current_rank')
                            ->label('Posición Actual')
                            ->numeric(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Colaborador')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Correo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pool.name')
                    ->label('Quiniela')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'blocked',
                        'secondary' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => [
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        'blocked' => 'Bloqueado',
                    ][$state] ?? $state),
                Tables\Columns\TextColumn::make('total_points')
                    ->label('Puntos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_rank')
                    ->label('Posición')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pool_id')
                    ->label('Quiniela')
                    ->relationship('pool', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'approved' => 'Aprobado',
                        'rejected' => 'Rechazado',
                        'blocked' => 'Bloqueado',
                    ]),
            ])
            ->actions([
                \Filament\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (PoolParticipant $record) => $record->status === 'pending')
                    ->action(function (PoolParticipant $record, RankingService $rankingService) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);

                        // Recalculate ranking to include this new participant
                        $rankingService->recalculatePoolRankings($record->pool_id);

                        Notification::make()
                            ->title('Participante Aprobado')
                            ->body("{$record->user->name} ha sido aprobado para participar.")
                            ->success()
                            ->send();
                    }),

                \Filament\Actions\Action::make('block')
                    ->label('Bloquear')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PoolParticipant $record) => $record->status === 'approved')
                    ->action(function (PoolParticipant $record) {
                        $record->update(['status' => 'blocked']);

                        Notification::make()
                            ->title('Participante Bloqueado')
                            ->body("{$record->user->name} ha sido bloqueado de la quiniela.")
                            ->danger()
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
            'index' => Pages\ListPoolParticipants::route('/'),
            'create' => Pages\CreatePoolParticipant::route('/create'),
            'edit' => Pages\EditPoolParticipant::route('/{record}/edit'),
        ];
    }
}
