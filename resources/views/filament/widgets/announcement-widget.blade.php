<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center gap-x-3 mb-4">
            <x-filament::icon
                icon="heroicon-o-megaphone"
                class="h-6 w-6 text-gray-500"
            />
            <h2 class="text-lg font-bold tracking-tight">Pengumuman Terbaru</h2>
        </div>

        @php $announcements = $this->getAnnouncements(); @endphp

        @if($announcements->count() > 0)
            <div class="space-y-4">
                @foreach($announcements as $announcement)
                    <div @class([
                        'p-4 rounded-lg border-l-4',
                        'bg-blue-50 border-blue-400 text-blue-700' => $announcement->type === 'info',
                        'bg-green-50 border-green-400 text-green-700' => $announcement->type === 'success',
                        'bg-yellow-50 border-yellow-400 text-yellow-700' => $announcement->type === 'warning',
                        'bg-red-50 border-red-400 text-red-700' => $announcement->type === 'danger',
                    ])>
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold">{{ $announcement->title }}</h3>
                            <span class="text-xs opacity-70">{{ $announcement->published_at?->diffForHumans() }}</span>
                        </div>
                        <div class="mt-2 text-sm line-clamp-2 prose prose-sm max-w-none">
                            {!! $announcement->content !!}
                        </div>
                        <div class="mt-2 text-right">
                            <a href="{{ \App\Filament\Resources\AnnouncementResource::getUrl('view', ['record' => $announcement]) }}" class="text-xs font-bold underline">
                                Baca Selengkapnya
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 italic">Tidak ada pengumuman saat ini.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
