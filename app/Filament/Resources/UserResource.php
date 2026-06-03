<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static \UnitEnum|string|null $navigationGroup = 'Configuración';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre Completo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->unique(User::class, 'email', ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                            ->disabled(fn ($livewire) => $livewire instanceof Pages\EditUser),
                        Forms\Components\Select::make('role')
                            ->label('Rol del Sistema')
                            ->options([
                                'super_admin' => 'Super Administrador',
                                'admin_quiniela' => 'Administrador de Quiniela',
                                'moderador' => 'Moderador',
                                'participante' => 'Participante Colaborador',
                                'invitado' => 'Invitado',
                            ])
                            ->required()
                            ->default('participante'),
                        Forms\Components\Select::make('status')
                            ->label('Estado de Cuenta')
                            ->options([
                                'active' => 'Activo',
                                'pending' => 'Pendiente de Aprobación',
                                'blocked' => 'Bloqueado',
                            ])
                            ->required()
                            ->default('active'),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono / WhatsApp')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('employee_code')
                            ->label('Documento Identificación (DPI)')
                            ->required()
                            ->minLength(13)
                            ->maxLength(20)
                            ->regex('/^[0-9]+$/')
                            ->unique(User::class, 'employee_code', ignoreRecord: true),
                        Forms\Components\Select::make('department_id')
                            ->label('Departamento')
                            ->relationship('department', 'name')
                            ->nullable(),
                        Forms\Components\Select::make('branch_id')
                            ->label('Sucursal')
                            ->relationship('branch', 'name')
                            ->nullable(),
                        Forms\Components\TextInput::make('company')
                            ->label('Empresa')
                            ->required()
                            ->default('Distribuidora Mariscal')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('accepted_terms')
                            ->label('Aceptó Términos')
                            ->default(false),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Rol')
                    ->colors([
                        'danger' => 'super_admin',
                        'warning' => 'admin_quiniela',
                        'success' => 'moderador',
                        'primary' => 'participante',
                        'secondary' => 'invitado',
                    ])
                    ->formatStateUsing(fn ($state) => [
                        'super_admin' => 'Super Admin',
                        'admin_quiniela' => 'Admin Quiniela',
                        'moderador' => 'Moderador',
                        'participante' => 'Participante',
                        'invitado' => 'Invitado',
                    ][$state] ?? $state),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => 'blocked',
                    ])
                    ->formatStateUsing(fn ($state) => [
                        'active' => 'Activo',
                        'pending' => 'Pendiente',
                        'blocked' => 'Bloqueado',
                    ][$state] ?? $state),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departamento')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin_quiniela' => 'Admin Quiniela',
                        'moderador' => 'Moderador',
                        'participante' => 'Participante',
                        'invitado' => 'Invitado',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Activo',
                        'pending' => 'Pendiente',
                        'blocked' => 'Bloqueado',
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
