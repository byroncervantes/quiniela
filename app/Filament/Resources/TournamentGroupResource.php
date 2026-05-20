<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TournamentGroupResource\Pages;
use App\Models\TournamentGroup;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TournamentGroupResource extends Resource
{
    protected static ?string $model = TournamentGroup::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-folder-open';

    protected static \UnitEnum|string|null $navigationGroup = 'Datos Deportivos';

    protected static ?string $modelLabel = 'Grupo';

    protected static ?string $pluralModelLabel = 'Grupos';

    protected static ?int $navigationSort = 2;

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
                            ->label('Nombre del Grupo (ej. Grupo A)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label('Código de Grupo (ej. A)')
                            ->required()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('order')
                            ->label('Orden de Visualización')
                            ->required()
                            ->numeric()
                            ->default(0),
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
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Orden')
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
            'index' => Pages\ListTournamentGroups::route('/'),
            'create' => Pages\CreateTournamentGroup::route('/create'),
            'edit' => Pages\EditTournamentGroup::route('/{record}/edit'),
        ];
    }
}
