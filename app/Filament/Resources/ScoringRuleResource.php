<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScoringRuleResource\Pages;
use App\Models\ScoringRule;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScoringRuleResource extends Resource
{
    protected static ?string $model = ScoringRule::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static \UnitEnum|string|null $navigationGroup = 'Configuración';

    protected static ?string $modelLabel = 'Regla de Puntos';

    protected static ?string $pluralModelLabel = 'Reglas de Puntos';

    protected static ?int $navigationSort = 1;

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
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la Regla')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('key')
                            ->label('Llave (Código)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('points')
                            ->label('Puntos a otorgar')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->label('Llave')
                    ->sortable(),
                Tables\Columns\TextColumn::make('points')
                    ->label('Puntos')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tournament.name')
                    ->label('Torneo')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tournament_id')
                    ->relationship('tournament', 'name'),
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
            'index' => Pages\ListScoringRules::route('/'),
            'create' => Pages\CreateScoringRule::route('/create'),
            'edit' => Pages\EditScoringRule::route('/{record}/edit'),
        ];
    }
}
