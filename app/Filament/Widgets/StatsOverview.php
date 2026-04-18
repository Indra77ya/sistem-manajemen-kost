<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Room;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        $roomsQuery = Room::query();
        $invoicesQuery = Invoice::query();
        $branchesQuery = Branch::query();

        return [
            Stat::make('Total Kamar', $roomsQuery->count())
                ->description('Semua kamar di cabang anda')
                ->descriptionIcon('heroicon-m-key'),
            Stat::make('Kamar Tersedia', $roomsQuery->where('status', 'available')->count())
                ->description('Siap untuk disewakan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($invoicesQuery->where('status', 'paid')->whereMonth('updated_at', now()->month)->sum('amount'), 0, ',', '.'))
                ->description('Total tagihan dibayar')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format(Expense::whereMonth('date', now()->month)->sum('amount'), 0, ',', '.'))
                ->description('Total pengeluaran operasional')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Booking Menunggu', \App\Models\Booking::where('status', 'pending')->count())
                ->description('Perlu konfirmasi pembayaran')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
