<?php

namespace App\Filament\Widgets;

use App\Models\MaintenanceRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MaintenanceCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Komplain per Kategori';

    protected function getData(): array
    {
        $data = MaintenanceRequest::query()
            ->join('maintenance_categories', 'maintenance_requests.maintenance_category_id', '=', 'maintenance_categories.id')
            ->select('maintenance_categories.name', DB::raw('count(*) as total'))
            ->groupBy('maintenance_categories.name')
            ->pluck('total', 'name')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Komplain',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40',
                    ],
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
