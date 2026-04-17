<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use Filament\Widgets\ChartWidget;

class RoomOccupancyChart extends ChartWidget
{
    protected static ?string $heading = 'Okupansi Kamar';

    protected function getData(): array
    {
        $available = Room::where('status', 'available')->count();
        $occupied = Room::where('status', 'occupied')->count();
        $maintenance = Room::where('status', 'maintenance')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Status Kamar',
                    'data' => [$available, $occupied, $maintenance],
                    'backgroundColor' => ['#4ADE80', '#F87171', '#FBBF24'],
                ],
            ],
            'labels' => ['Tersedia', 'Terisi', 'Perbaikan'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
