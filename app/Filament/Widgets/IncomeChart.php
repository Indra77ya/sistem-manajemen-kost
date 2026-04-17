<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan vs Pengeluaran';

    protected function getData(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        // Income Data
        $incomeQuery = Invoice::where('status', 'paid')
            ->whereYear('updated_at', date('Y'));

        if ($driver === 'sqlite') {
            $incomeQuery->select(
                DB::raw('sum(amount) as total'),
                DB::raw("strftime('%m', updated_at) as month")
            );
        } else {
            $incomeQuery->select(
                DB::raw('sum(amount) as total'),
                DB::raw("DATE_FORMAT(updated_at, '%m') as month")
            );
        }

        $incomeData = $incomeQuery->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Expense Data
        $expenseQuery = Expense::whereYear('date', date('Y'));

        if ($driver === 'sqlite') {
            $expenseQuery->select(
                DB::raw('sum(amount) as total'),
                DB::raw("strftime('%m', date) as month")
            );
        } else {
            $expenseQuery->select(
                DB::raw('sum(amount) as total'),
                DB::raw("DATE_FORMAT(date, '%m') as month")
            );
        }

        $expenseData = $expenseQuery->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
        ];

        $chartIncomeData = [];
        $chartExpenseData = [];
        $labels = [];

        foreach ($months as $key => $name) {
            $labels[] = $name;
            $chartIncomeData[] = $incomeData[$key] ?? 0;
            $chartExpenseData[] = $expenseData[$key] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $chartIncomeData,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                ],
                [
                    'label' => 'Pengeluaran (Rp)',
                    'data' => $chartExpenseData,
                    'backgroundColor' => '#FF6384',
                    'borderColor' => '#FF6384',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
