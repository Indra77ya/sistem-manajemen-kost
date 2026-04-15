<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaseResource\Pages;
use App\Models\Lease;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaseResource extends Resource
{
    protected static ?string $model = Lease::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('tenant', 'name', fn ($query) => $query->where('role', 'tenant'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('room_id')
                            ->relationship('room', 'room_number')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $room = Room::find($state);
                                    $set('billing_amount', $room?->price);
                                    $set('branch_id', $room?->branch_id);
                                }
                            })
                            ->rules([
                                function (Forms\Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        if (!$value) return;

                                        $room = Room::find($value);
                                        if (!$room) return;

                                        $activeLeasesCount = Lease::where('room_id', $value)
                                            ->where('status', 'active')
                                            ->where('id', '!=', $get('id')) // Ignore current lease if editing
                                            ->count();

                                        if ($activeLeasesCount >= $room->capacity) {
                                            $fail("Kamar sudah penuh. Kapasitas: {$room->capacity}, Terisi: {$activeLeasesCount}.");
                                        }
                                    };
                                },
                            ]),
                        Forms\Components\Hidden::make('branch_id'),
                        Forms\Components\DatePicker::make('start_date')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date'),
                        Forms\Components\TextInput::make('billing_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'ended' => 'Ended',
                            ])
                            ->required(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label('Room')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.branch.name')
                    ->label('Branch')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('billing_amount')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'ended' => 'danger',
                        'pending' => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'ended' => 'Ended',
                        'pending' => 'Pending',
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
            'index' => Pages\ListLeases::route('/'),
            'create' => Pages\CreateLease::route('/create'),
            'edit' => Pages\EditLease::route('/{record}/edit'),
        ];
    }
}
