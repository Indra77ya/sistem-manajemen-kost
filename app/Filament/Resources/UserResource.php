<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\Select::make('role')
                            ->options(function () {
                                $roles = [
                                    'owner' => 'Owner',
                                    'admin' => 'Admin',
                                    'tenant' => 'Tenant',
                                ];
                                if (auth()->user()->role === 'developer') {
                                    $roles['developer'] = 'Developer';
                                }
                                return $roles;
                            })
                            ->required(),
                        Forms\Components\TextInput::make('phone_number')
                            ->tel(),
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->label('Assigned Branch')
                            ->helperText('Main branch for Admin or Tenant'),
                        Forms\Components\Select::make('branches')
                            ->multiple()
                            ->relationship('branches', 'name')
                            ->label('Managed Branches')
                            ->visible(fn (Forms\Get $get) => $get('role') === 'admin')
                            ->helperText('Additional branches this admin can manage'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'developer' => 'danger',
                        'owner' => 'warning',
                        'admin' => 'success',
                        'tenant' => 'info',
                    }),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Primary Branch')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'owner' => 'Owner',
                        'admin' => 'Admin',
                        'tenant' => 'Tenant',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
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
