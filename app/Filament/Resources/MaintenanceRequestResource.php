<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceRequestResource\Pages;
use App\Filament\Resources\MaintenanceRequestResource\RelationManagers;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class MaintenanceRequestResource extends Resource
{
    protected static ?string $model = MaintenanceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Layanan';

    protected static ?string $label = 'Komplain';

    protected static ?string $pluralLabel = 'Komplain';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->default(fn () => \App\Models\Lease::where('user_id', auth()->id())->where('status', 'active')->first()?->branch_id)
                            ->disabled(fn () => auth()->user()->role === User::ROLE_TENANT && \App\Models\Lease::where('user_id', auth()->id())->where('status', 'active')->exists())
                            ->dehydrated(),
                        Forms\Components\Select::make('room_id')
                            ->label('Kamar')
                            ->relationship('room', 'number', fn (Builder $query, Forms\Get $get) =>
                                $query->where('branch_id', $get('branch_id'))
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn () => \App\Models\Lease::where('user_id', auth()->id())->where('status', 'active')->first()?->room_id)
                            ->disabled(fn () => auth()->user()->role === User::ROLE_TENANT && \App\Models\Lease::where('user_id', auth()->id())->where('status', 'active')->exists())
                            ->dehydrated(),
                        Forms\Components\Select::make('user_id')
                            ->label('Penyewa')
                            ->relationship('tenant', 'name', fn (Builder $query) => $query->where('role', User::ROLE_TENANT))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->user()->role === User::ROLE_TENANT ? auth()->id() : null)
                            ->disabled(fn () => auth()->user()->role === User::ROLE_TENANT)
                            ->dehydrated(),
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Keluhan')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('attachment_before')
                            ->label('Foto Sebelum Perbaikan')
                            ->image()
                            ->directory('maintenance-attachments'),
                    ])->columns(2),

                Forms\Components\Section::make('Penanganan & Biaya')
                    ->schema([
                        Forms\Components\Select::make('technician_id')
                            ->label('Teknisi')
                            ->relationship('technician', 'name', fn (Builder $query) => $query->where('role', User::ROLE_TECHNICIAN))
                            ->searchable()
                            ->preload()
                            ->disabled(fn () => !in_array(auth()->user()->role, [User::ROLE_ADMIN, User::ROLE_OWNER, User::ROLE_DEVELOPER])),
                        Forms\Components\Select::make('priority')
                            ->label('Prioritas')
                            ->options([
                                'low' => 'Rendah',
                                'normal' => 'Normal',
                                'high' => 'Tinggi',
                                'urgent' => 'Mendesak',
                            ])
                            ->required()
                            ->default('normal'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Menunggu',
                                'in_progress' => 'Diproses',
                                'resolved' => 'Selesai',
                                'closed' => 'Ditutup',
                            ])
                            ->required()
                            ->default('pending')
                            ->disabled(fn () => auth()->user()->role === User::ROLE_TENANT),
                        Forms\Components\TextInput::make('total_cost')
                            ->label('Total Biaya')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->disabled(fn () => auth()->user()->role === User::ROLE_TENANT),
                        Forms\Components\Toggle::make('is_charged_to_tenant')
                            ->label('Bebankan ke Penyewa?')
                            ->default(false)
                            ->disabled(fn () => auth()->user()->role === User::ROLE_TENANT),
                        Forms\Components\FileUpload::make('attachment_after')
                            ->label('Foto Sesudah Perbaikan')
                            ->image()
                            ->directory('maintenance-attachments')
                            ->disabled(fn () => auth()->user()->role === User::ROLE_TENANT),
                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('Mulai Dikerjakan')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->label('Selesai Dikerjakan')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('room.number')
                    ->label('Kamar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Penyewa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Teknisi')
                    ->sortable()
                    ->placeholder('Belum ditunjuk'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
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
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'in_progress' => 'Diproses',
                        'resolved' => 'Selesai',
                        'closed' => 'Ditutup',
                    }),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'in_progress' => 'Diproses',
                        'resolved' => 'Selesai',
                        'closed' => 'Ditutup',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Rendah',
                        'normal' => 'Normal',
                        'high' => 'Tinggi',
                        'urgent' => 'Mendesak',
                    ]),
            ])
            ->actions([
                Action::make('start_work')
                    ->label('Mulai Kerja')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(fn (MaintenanceRequest $record) =>
                        $record->status === 'pending' &&
                        (auth()->user()->role !== User::ROLE_TECHNICIAN || $record->technician_id === auth()->id())
                    )
                    ->action(function (MaintenanceRequest $record) {
                        $record->update([
                            'status' => 'in_progress',
                            'started_at' => now(),
                        ]);
                        Notification::make()->title('Perbaikan dimulai').success()->send();
                    }),
                Action::make('resolve_work')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (MaintenanceRequest $record) =>
                        $record->status === 'in_progress' &&
                        (auth()->user()->role !== User::ROLE_TECHNICIAN || $record->technician_id === auth()->id())
                    )
                    ->form([
                        Forms\Components\FileUpload::make('attachment_after')
                            ->label('Foto Bukti Selesai')
                            ->image()
                            ->required()
                            ->directory('maintenance-attachments'),
                        Forms\Components\TextInput::make('total_cost')
                            ->label('Total Biaya')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                    ])
                    ->action(function (MaintenanceRequest $record, array $data) {
                        $record->update([
                            'status' => 'resolved',
                            'resolved_at' => now(),
                            'attachment_after' => $data['attachment_after'],
                            'total_cost' => $data['total_cost'],
                        ]);
                        Notification::make()->title('Perbaikan selesai').success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceRequests::route('/'),
            'create' => Pages\CreateMaintenanceRequest::route('/create'),
            'edit' => Pages\EditMaintenanceRequest::route('/{record}/edit'),
        ];
    }
}
