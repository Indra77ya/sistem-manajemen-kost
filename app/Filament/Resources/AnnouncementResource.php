<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $label = 'Pengumuman';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Target Cabang')
                            ->relationship('branch', 'name')
                            ->placeholder('Semua Cabang (Global)')
                            ->searchable()
                            ->preload()
                            ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'owner'])),
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Pengumuman')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content')
                            ->label('Isi Pengumuman')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'info' => 'Informasi (Biru)',
                                'success' => 'Penting (Hijau)',
                                'warning' => 'Peringatan (Kuning)',
                                'danger' => 'Mendesak (Merah)',
                            ])
                            ->required()
                            ->default('info'),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Tanggal Terbit')
                            ->default(now())
                            ->required(),
                        Forms\Components\DateTimePicker::make('expired_at')
                            ->label('Tanggal Kedaluwarsa')
                            ->placeholder('Biarkan kosong jika tidak ada batas'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->placeholder('Global')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'info' => 'info',
                        'success' => 'success',
                        'warning' => 'warning',
                        'danger' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Terbit')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expired_at')
                    ->label('Berakhir')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Tidak ada'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name')
                    ->placeholder('Semua'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => !Auth::user()->hasRole('tenant')),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => !Auth::user()->hasRole('tenant')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->visible(fn () => !Auth::user()->hasRole('tenant')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
            'view' => Pages\ViewAnnouncement::route('/{record}'),
        ];
    }
}
