<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $branch->name }} - KostManage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-md p-4">
        <div class="container mx-auto">
            <a href="/" class="text-blue-600 font-bold">← Kembali ke Beranda</a>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-12">
        <div class="mb-12">
            <h1 class="text-4xl font-bold mb-4">{{ $branch->name }}</h1>
            <p class="text-gray-600 text-lg"><i class="fas fa-map-marker-alt mr-2"></i>{{ $branch->address }}</p>
        </div>

        <h2 class="text-2xl font-bold mb-8">Kamar Tersedia di Cabang Ini</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($rooms as $room)
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2">Kamar {{ $room->room_number }}</h3>
                        <p class="text-blue-600 font-bold mb-4 text-lg">Rp {{ number_format($room->price, 0, ',', '.') }}/bln</p>
                        <div class="flex items-center text-sm text-gray-500 mb-6">
                            <span class="mr-4"><i class="fas fa-user mr-1"></i> {{ $room->capacity }} Orang</span>
                            <span><i class="fas fa-tag mr-1"></i> {{ $room->type }}</span>
                        </div>
                        <a href="{{ route('room.detail', $room->id) }}" class="block text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition font-bold">Detail Kamar</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
