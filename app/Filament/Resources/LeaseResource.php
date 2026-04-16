<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaseResource\Pages;
use App\Filament\Resources\LeaseResource\RelationManagers;
use App\Models\Lease;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class LeaseResource extends Resource
{
    protected static ?string $model = Lease::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $label = 'Sewa';

    protected static ?string $pluralLabel = 'Sewa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\Select::make('room_id')
                            ->label('Kamar')
                            ->relationship('room', 'number', fn (Builder $query, Forms\Get $get, ?Lease $record) =>
                                $query->where('branch_id', $get('branch_id'))
                                      ->where(fn ($q) => $q->where('status', 'available')->orWhere('id', $record?->room_id))
                            )
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('user_id')
                            ->label('Penyewa')
                            ->relationship('tenant', 'name', fn (Builder $query) => $query->where('role', 'tenant'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('email')->email()->required()->unique('users', 'email'),
                                Forms\Components\TextInput::make('password')->password()->required()->default('password'),
                                Forms\Components\Hidden::make('role')->default('tenant'),
                            ]),
                    ])->columns(3),

                Forms\Components\Section::make('Detail Sewa & Biaya')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai'),
                        Forms\Components\TextInput::make('billing_date')
                            ->label('Tanggal Tagihan (Tiap Bulan)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31)
                            ->default(1),
                        Forms\Components\TextInput::make('deposit_amount')
                            ->label('Uang Jaminan (Deposit)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\CheckboxList::make('services')
                            ->label('Layanan Tambahan')
                            ->relationship('services', 'name', fn (Builder $query, Forms\Get $get) =>
                                $query->where('branch_id', $get('branch_id'))
                            )
                            ->columns(2),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Aktif',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('active'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.number')
                    ->label('Kamar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Penyewa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deposit_amount')
                    ->label('Deposit')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('checkout')
                    ->label('Check-out')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->visible(fn (Lease $record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Check-out')
                    ->modalDescription('Sistem akan menghitung penyelesaian deposit dan menaikkan status sewa menjadi Selesai.')
                    ->action(function (Lease $record) {
                        $unpaidAmount = $record->invoices()->where('status', '!=', 'paid')->sum('amount');

                        // Calculate maintenance costs charged to tenant for this specific lease period
                        $maintenanceCosts = \App\Models\MaintenanceRequest::where('room_id', $record->room_id)
                            ->where('user_id', $record->user_id)
                            ->where('is_charged_to_tenant', true)
                            ->where('status', 'resolved')
                            ->where('created_at', '>=', $record->start_date)
                            ->sum('total_cost');

                        $refundAmount = $record->deposit_amount - $unpaidAmount - $maintenanceCosts;

                        $record->update(['status' => 'completed', 'end_date' => now()]);
                        $record->room->update(['status' => 'available']);

                        $body = "Sewa selesai. ";
                        if ($maintenanceCosts > 0) {
                            $body .= "Biaya perbaikan: Rp " . number_format($maintenanceCosts, 0, ',', '.') . ". ";
                        }
                        $body .= "Sisa deposit yang harus dikembalikan: Rp " . number_format($refundAmount, 0, ',', '.');

                        Notification::make()
                            ->title('Check-out Berhasil')
                            ->body($body)
                            ->success()
                            ->send();
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
            'index' => Pages\ListLeases::route('/'),
            'create' => Pages\CreateLease::route('/create'),
            'edit' => Pages\EditLease::route('/{record}/edit'),
        ];
    }
}
