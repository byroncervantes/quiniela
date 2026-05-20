<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoolResource\Pages;
use App\Models\Pool;
use App\Models\Tournament;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PoolResource extends Resource
{
    protected static ?string $model = Pool::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static \UnitEnum|string|null $navigationGroup = 'Quiniela';

    protected static ?string $modelLabel = 'Quiniela';

    protected static ?string $pluralModelLabel = 'Quinielas';

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
                            ->label('Nombre de la Quiniela')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(Pool::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('visibility')
                            ->label('Visibilidad')
                            ->options([
                                'public' => 'Pública',
                                'private' => 'Privada',
                            ])
                            ->required()
                            ->default('public'),
                        Forms\Components\Select::make('join_mode')
                            ->label('Modo de Ingreso')
                            ->options([
                                'open' => 'Abierto (Auto-aprobado)',
                                'approval_required' => 'Requiere Aprobación',
                                'invitation_code' => 'Por Código de Invitación',
                            ])
                            ->required()
                            ->default('open')
                            ->reactive(),
                        Forms\Components\TextInput::make('invitation_code')
                            ->label('Código de Invitación')
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('join_mode') === 'invitation_code'),
                        Forms\Components\TextInput::make('max_participants')
                            ->label('Máximo de Participantes (Opcional)')
                            ->numeric(),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Fecha de Inicio'),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Fecha de Fin'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                        Forms\Components\Select::make('created_by')
                            ->label('Creado por')
                            ->relationship('creator', 'name')
                            ->required()
                            ->default(auth()->id()),
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
                Tables\Columns\TextColumn::make('tournament.name')
                    ->label('Torneo')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('visibility')
                    ->label('Visibilidad')
                    ->colors([
                        'success' => 'public',
                        'primary' => 'private',
                    ])
                    ->formatStateUsing(fn ($state) => [
                        'public' => 'Pública',
                        'private' => 'Privada',
                    ][$state] ?? $state),
                Tables\Columns\BadgeColumn::make('join_mode')
                    ->label('Modo de Ingreso')
                    ->colors([
                        'success' => 'open',
                        'warning' => 'approval_required',
                        'primary' => 'invitation_code',
                    ])
                    ->formatStateUsing(fn ($state) => [
                        'open' => 'Abierto',
                        'approval_required' => 'Aprobación',
                        'invitation_code' => 'Código',
                    ][$state] ?? $state),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('participants_count')
                    ->label('Participantes')
                    ->counts('participants'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('visibility')
                    ->options([
                        'public' => 'Pública',
                        'private' => 'Privada',
                    ]),
                Tables\Filters\SelectFilter::make('join_mode')
                    ->options([
                        'open' => 'Abierto',
                        'approval_required' => 'Aprobación',
                        'invitation_code' => 'Código',
                    ]),
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
            'index' => Pages\ListPools::route('/'),
            'create' => Pages\CreatePool::route('/create'),
            'edit' => Pages\EditPool::route('/{record}/edit'),
        ];
    }
}
