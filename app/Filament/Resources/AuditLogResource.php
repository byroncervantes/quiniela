<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static \UnitEnum|string|null $navigationGroup = 'Configuración';

    protected static ?string $modelLabel = 'Auditoría';

    protected static ?string $pluralModelLabel = 'Auditorías';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Usuario Administrador')
                            ->disabled(),
                        Forms\Components\TextInput::make('action')
                            ->label('Acción')
                            ->disabled(),
                        Forms\Components\TextInput::make('auditable_type')
                            ->label('Tipo de Entidad')
                            ->disabled(),
                        Forms\Components\TextInput::make('auditable_id')
                            ->label('ID Entidad')
                            ->disabled(),
                        Forms\Components\KeyValue::make('old_values')
                            ->label('Valores Anteriores')
                            ->disabled(),
                        Forms\Components\KeyValue::make('new_values')
                            ->label('Valores Nuevos')
                            ->disabled(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('Dirección IP')
                            ->disabled(),
                        Forms\Components\TextInput::make('user_agent')
                            ->label('Navegador / Dispositivo')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Administrador')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Acción Realizada')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                \Filament\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
