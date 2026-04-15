<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Kost Multi Cabang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-900">
    <!-- Navbar -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="/" class="text-2xl font-bold text-blue-600">KostManage</a>
            <div class="hidden md:flex space-x-8 items-center">
                <a href="#cabang" class="hover:text-blue-600">Cabang</a>
                <a href="#kamar" class="hover:text-blue-600">Kamar Tersedia</a>
                <a href="/admin/login" class="bg-blue-600 text-white px-6 py-2 rounded-full hover:bg-blue-700 transition">Login Admin</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="bg-blue-600 text-white py-24">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-5xl font-extrabold mb-6">Temukan Hunian Kost Terbaik Anda</h1>
            <p class="text-xl mb-10 opacity-90">Sistem manajemen kost modern dengan berbagai cabang di lokasi strategis.</p>
            <a href="#cabang" class="bg-white text-blue-600 px-10 py-4 rounded-full font-bold text-lg hover:bg-gray-100 transition shadow-lg">Cari Sekarang</a>
        </div>
    </header>

    <!-- Cabang Section -->
    <section id="cabang" class="py-20">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold mb-12 text-center">Cabang Kami</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                @foreach($branches as $branch)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition duration-300">
                    @if($branch->image)
                        <img src="{{ asset('storage/' . $branch->image) }}" class="w-full h-56 object-cover" alt="{{ $branch->name }}">
                    @else
                        <div class="w-full h-56 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-building text-4xl text-gray-400"></i>
                        </div>
                    @endif
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2">{{ $branch->name }}</h3>
                        <p class="text-gray-600 mb-4 line-clamp-2">{{ $branch->address }}</p>
                        <a href="{{ route('branch.detail', $branch->slug) }}" class="text-blue-600 font-semibold hover:underline">Lihat Detail →</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Kamar Section -->
    <section id="kamar" class="py-20 bg-gray-100">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold mb-12 text-center">Kamar Pilihan</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                @foreach($featuredRooms as $room)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="relative">
                        @php $primaryImage = $room->images->first(); @endphp
                        @if($primaryImage)
                            <img src="{{ asset('storage/' . $primaryImage->image_path) }}" class="w-full h-56 object-cover" alt="Room {{ $room->room_number }}">
                        @else
                            <div class="w-full h-56 bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-door-open text-4xl text-gray-400"></i>
                            </div>
                        @endif
                        <div class="absolute top-4 right-4 bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-bold">
                            {{ $room->type }}
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold">Kamar {{ $room->room_number }}</h3>
                                <p class="text-sm text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i> {{ $room->branch->name }}</p>
                            </div>
                            <p class="text-blue-600 font-bold">Rp {{ number_format($room->price, 0, ',', '.') }}<span class="text-xs text-gray-500">/bln</span></p>
                        </div>
                        <div class="flex items-center space-x-4 mb-6 text-sm text-gray-600">
                            <span><i class="fas fa-user mr-1"></i> {{ $room->capacity }} Orang</span>
                            <span><i class="fas fa-check-circle text-green-500 mr-1"></i> Tersedia</span>
                        </div>
                        <a href="{{ route('room.detail', $room->id) }}" class="block text-center bg-gray-900 text-white py-3 rounded-lg hover:bg-gray-800 transition font-bold">Detail Kamar</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-6 text-center">
            <p class="opacity-60">&copy; 2024 KostManage. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
