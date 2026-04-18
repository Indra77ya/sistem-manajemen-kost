<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Lease;
use App\Models\Room;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Manajemen Sewa';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Booking')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('room_id', null))
                            ->disabled(fn (string $operation) => $operation !== 'create'),
                        Forms\Components\Select::make('room_id')
                            ->label('Kamar')
                            ->options(function (Forms\Get $get) {
                                $branchId = $get('branch_id');
                                if (!$branchId) return [];
                                return Room::where('branch_id', $branchId)
                                    ->where(function ($query) {
                                        $query->where('status', 'available')
                                              ->orWhere('status', 'reserved');
                                    })
                                    ->pluck('number', 'id');
                            })
                            ->required()
                            ->disabled(fn (string $operation) => $operation !== 'create'),
                        Forms\Components\Select::make('user_id')
                            ->label('Penyewa')
                            ->relationship('tenant', 'name', fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'tenant')))
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('check_in_date')
                            ->label('Tanggal Masuk')
                            ->required()
                            ->minDate(now()),
                        Forms\Components\TextInput::make('booking_fee')
                            ->label('Biaya Booking')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(fn (Forms\Get $get) => Branch::find($get('branch_id'))?->default_booking_fee ?? 0),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Menunggu Pembayaran',
                                'confirmed' => 'Dikonfirmasi',
                                'cancelled' => 'Dibatalkan',
                                'completed' => 'Selesai (Check-in)',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\FileUpload::make('proof_of_payment')
                            ->label('Bukti Pembayaran')
                            ->directory('booking-payments')
                            ->image(),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Batas Waktu Pembayaran')
                            ->default(fn (Forms\Get $get) => now()->addHours(Branch::find($get('branch_id'))?->booking_expiration_hours ?? 24)),
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
                Tables\Columns\TextColumn::make('check_in_date')
                    ->label('Tgl Masuk')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_fee')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'cancelled' => 'Batal',
                        'completed' => 'Selesai',
                    }),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Kadaluarsa')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($record) => $record->status === 'pending' && $record->expires_at < now() ? 'danger' : null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'cancelled' => 'Batal',
                        'completed' => 'Selesai',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Konfirmasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->hidden(fn (Booking $record) => $record->status !== 'pending')
                    ->action(function (Booking $record) {
                        $record->update(['status' => 'confirmed']);
                        Notification::make()
                            ->title('Booking dikonfirmasi')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('check_in')
                    ->label('Check-in')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('info')
                    ->hidden(fn (Booking $record) => $record->status !== 'confirmed')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai Sewa')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('billing_date')
                            ->label('Tanggal Tagihan')
                            ->numeric()
                            ->default(now()->day)
                            ->required(),
                    ])
                    ->action(function (Booking $record, array $data) {
                        // Create Lease
                        $lease = Lease::create([
                            'branch_id' => $record->branch_id,
                            'room_id' => $record->room_id,
                            'user_id' => $record->user_id,
                            'start_date' => $data['start_date'],
                            'billing_date' => $data['billing_date'],
                            'status' => 'active',
                            'deposit_amount' => $record->booking_fee, // Transfer fee to deposit
                            'booking_id' => $record->id,
                        ]);

                        $record->update(['status' => 'completed']);

                        Notification::make()
                            ->title('Check-in berhasil, data sewa telah dibuat')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'view' => Pages\ViewBooking::route('/{record}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
