<?php

namespace App\Filament\Widgets;

use App\Models\Announcement;
use Filament\Widgets\Widget;

class AnnouncementWidget extends Widget
{
    protected static string $view = 'filament.widgets.announcement-widget';

    protected static ?int $sort = -10; // Show at the top

    public function getAnnouncements()
    {
        return Announcement::query()
            ->where(function ($query) {
                $query->where('published_at', '<=', now())
                      ->orWhereNull('published_at');
            })
            ->where(function ($query) {
                $query->where('expired_at', '>=', now())
                      ->orWhereNull('expired_at');
            })
            ->latest()
            ->get();
    }
}
