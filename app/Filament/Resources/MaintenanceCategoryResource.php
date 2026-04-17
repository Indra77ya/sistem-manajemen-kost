<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceCategoryResource\Pages;
use App\Models\MaintenanceCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceCategoryResource extends Resource
{
    protected static ?string $model = MaintenanceCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $label = 'Kategori Komplain';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('default_priority')
                    ->label('Prioritas Bawaan')
                    ->options([
                        'low' => 'Rendah',
                        'normal' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                    ])
                    ->required()
                    ->default('normal'),
                Forms\Components\Select::make('default_technician_id')
                    ->label('Teknisi Bawaan (Opsional)')
                    ->relationship('defaultTechnician', 'name', fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'technician')))
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('default_priority')
                    ->label('Prioritas Bawaan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'info',
                        'normal' => 'success',
                        'high' => 'warning',
                        'urgent' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'normal' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                    }),
                Tables\Columns\TextColumn::make('defaultTechnician.name')
                    ->label('Teknisi Bawaan')
                    ->placeholder('Tidak ada'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceCategories::route('/'),
            'create' => Pages\CreateMaintenanceCategory::route('/create'),
            'edit' => Pages\EditMaintenanceCategory::route('/{record}/edit'),
        ];
    }
}
