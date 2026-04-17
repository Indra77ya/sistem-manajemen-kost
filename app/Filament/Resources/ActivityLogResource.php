<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Spatie\Activitylog\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-history';

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?string $label = 'Log Aktivitas';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Pelaku')
                    ->searchable()
                    ->placeholder('System'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Aksi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Modul')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('properties.attributes')
                    ->label('Detail Perubahan')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('Modul')
                    ->options([
                        \App\Models\Room::class => 'Kamar',
                        \App\Models\Invoice::class => 'Tagihan',
                        \App\Models\Lease::class => 'Sewa',
                        \App\Models\User::class => 'User',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageActivityLogs::route('/'),
        ];
    }

    public static function canCreate(): bool => false;
}
