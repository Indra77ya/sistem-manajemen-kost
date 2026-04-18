<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Kost - Cari Kamar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <div>
                        <a href="/" class="flex items-center py-4 px-2">
                            <span class="font-semibold text-gray-500 text-lg">KostKita</span>
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="/admin" class="py-2 px-2 font-medium text-gray-500 rounded hover:bg-blue-500 hover:text-white transition duration-300">Login Admin/Penyewa</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto mt-10 px-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Cari Kamar Kost Tersedia</h1>

        <form action="/" method="GET" class="mb-10 bg-white p-6 rounded-lg shadow-md flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700">Pilih Cabang</label>
                <select name="branch_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-300">Filter</button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-20">
            @forelse($rooms as $room)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition duration-300">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <h2 class="text-xl font-bold text-gray-800">Kamar {{ $room->number }}</h2>
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $room->type }}</span>
                        </div>
                        <p class="text-gray-600 mt-2">{{ $room->branch->name }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ $room->branch->address }}</p>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-2xl font-bold text-blue-600">Rp {{ number_format($room->price, 0, ',', '.') }}<span class="text-sm text-gray-500 font-normal">/bulan</span></span>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            <p>Kapasitas: {{ $room->capacity }} Orang</p>
                            <p class="mt-2">{{ Str::limit($room->description, 100) }}</p>
                        </div>
                        <a href="{{ route('booking.create', $room) }}" class="mt-6 block text-center bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition duration-300">Pesan Sekarang</a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-10">
                    <p class="text-gray-500 text-xl">Maaf, saat ini tidak ada kamar yang tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>
