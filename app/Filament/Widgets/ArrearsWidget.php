<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ArrearsWidget extends BaseWidget
{
    protected static ?string $heading = 'Daftar Tunggakan';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::whereIn('status', ['unpaid', 'overdue', 'partially_paid'])
                    ->where('due_date', '<', now())
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lease.tenant.name')
                    ->label('Penyewa'),
                Tables\Columns\TextColumn::make('lease.room.number')
                    ->label('Kamar'),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Total Tagihan')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'overdue' => 'danger',
                        'partially_paid' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'Belum Bayar',
                        'overdue' => 'Terlambat',
                        'partially_paid' => 'Bayar Sebagian',
                    }),
            ]);
    }
}
