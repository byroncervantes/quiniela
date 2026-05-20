<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Models\Team;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-flag';

    protected static \UnitEnum|string|null $navigationGroup = 'Datos Deportivos';

    protected static ?string $modelLabel = 'Selección';

    protected static ?string $pluralModelLabel = 'Selecciones';

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
                        Forms\Components\Select::make('tournament_group_id')
                            ->label('Grupo')
                            ->relationship('group', 'name')
                            ->nullable(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del País')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('official_name')
                            ->label('Nombre Oficial')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('fifa_code')
                            ->label('Código FIFA (ej. ARG, MEX)')
                            ->required()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('country_code')
                            ->label('Código de País (ej. AR, MX)')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('flag_url')
                            ->label('URL de Bandera')
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('flag_path')
                            ->label('Subir Bandera Local')
                            ->image()
                            ->directory('flags'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('flag_url')
                    ->label('Bandera')
                    ->circular()
                    ->width('40px'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fifa_code')
                    ->label('FIFA')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('group.name')
                    ->label('Grupo')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tournament_group_id')
                    ->label('Grupo')
                    ->relationship('group', 'name'),
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
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
