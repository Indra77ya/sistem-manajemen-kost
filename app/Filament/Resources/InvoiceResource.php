<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $label = 'Tagihan';

    protected static ?string $pluralLabel = 'Tagihan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Header Tagihan')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('lease_id')
                            ->label('Sewa')
                            ->relationship('lease', 'id', fn (Builder $query) => $query->with(['room', 'tenant']))
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->tenant->name} - Kamar {$record->room->number}")
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Nomor Invoice')
                            ->required()
                            ->default(fn () => 'INV-' . strtoupper(uniqid())),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Jatuh Tempo')
                            ->required()
                            ->default(now()->addDays(7)),
                        Forms\Components\Select::make('status')
                            ->options([
                                'unpaid' => 'Belum Bayar',
                                'paid' => 'Lunas',
                                'overdue' => 'Terlambat',
                            ])
                            ->required()
                            ->default('unpaid'),
                        Forms\Components\TextInput::make('amount')
                            ->label('Total Tagihan')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(3),

                Forms\Components\Section::make('Rincian Item')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('description')
                                    ->label('Deskripsi')
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('amount')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                Forms\Components\Select::make('type')
                                    ->label('Tipe')
                                    ->options([
                                        'rent' => 'Sewa Kamar',
                                        'deposit' => 'Deposit',
                                        'service' => 'Layanan',
                                        'penalty' => 'Denda',
                                        'other' => 'Lainnya',
                                    ])
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $items = $get('items') ?? [];
                                $total = 0;
                                foreach ($items as $item) {
                                    $total += (float) ($item['amount'] ?? 0);
                                }
                                $set('amount', $total);
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lease.tenant.name')
                    ->label('Penyewa')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Nomor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'warning',
                        'overdue' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Lunas',
                        'unpaid' => 'Belum Bayar',
                        'overdue' => 'Terlambat',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('pay')
                    ->label('Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Invoice $record) => in_array($record->status, ['unpaid', 'overdue']))
                    ->form([
                        Forms\Components\FileUpload::make('proof_of_payment')
                            ->label('Unggah Bukti Pembayaran')
                            ->image()
                            ->directory('payments')
                            ->required(),
                        Forms\Components\Select::make('payment_method')
                            ->label('Metode')
                            ->options([
                                'transfer' => 'Transfer Bank',
                                'cash' => 'Tunai',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Tanggal Bayar')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        $record->payments()->create([
                            'branch_id' => $record->branch_id,
                            'amount' => $record->amount,
                            'payment_date' => $data['payment_date'],
                            'payment_method' => $data['payment_method'],
                            'proof_of_payment' => $data['proof_of_payment'],
                            'status' => 'pending',
                        ]);
                    }),
                Tables\Actions\Action::make('download')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Invoice $record) => route('invoice.download', $record))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
