<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PredictionResource\Pages;
use App\Models\Prediction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PredictionResource extends Resource
{
    protected static ?string $model = Prediction::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static \UnitEnum|string|null $navigationGroup = 'Quiniela';

    protected static ?string $modelLabel = 'Pronóstico';

    protected static ?string $pluralModelLabel = 'Pronósticos';

    protected static ?int $navigationSort = 4;

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
                            ->label('Usuario')
                            ->relationship('user', 'name')
                            ->required(),
                        Forms\Components\Select::make('match_id')
                            ->label('Partido')
                            ->relationship('match', 'match_number')
                            ->required(),
                        Forms\Components\TextInput::make('predicted_home_score')
                            ->label('Pronóstico Local')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('predicted_away_score')
                            ->label('Pronóstico Visitante')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('points_awarded')
                            ->label('Puntos Obtenidos')
                            ->numeric()
                            ->default(0),
                        Forms\Components\DateTimePicker::make('submitted_at')
                            ->label('Fecha de Envío')
                            ->required(),
                        Forms\Components\DateTimePicker::make('calculated_at')
                            ->label('Fecha de Cálculo'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pool.name')
                    ->label('Quiniela')
                    ->sortable(),
                Tables\Columns\TextColumn::make('match.match_number')
                    ->label('Partido #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('prediction')
                    ->label('Pronóstico')
                    ->formatStateUsing(fn (Prediction $record) => "{$record->predicted_home_score} - {$record->predicted_away_score}"),
                Tables\Columns\TextColumn::make('points_awarded')
                    ->label('Puntos')
                    ->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Enviado')
                    ->dateTime('d/m H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pool_id')
                    ->label('Quiniela')
                    ->relationship('pool', 'name'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPredictions::route('/'),
            'create' => Pages\CreatePrediction::route('/create'),
            'edit' => Pages\EditPrediction::route('/{record}/edit'),
        ];
    }
}
