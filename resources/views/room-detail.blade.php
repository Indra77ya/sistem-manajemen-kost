<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $room->room_number }} - {{ $room->branch->name }}</title>
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Galeri Foto -->
            <div>
                <div class="rounded-2xl overflow-hidden shadow-xl mb-6">
                    @if($room->images->count() > 0)
                        <img src="{{ asset('storage/' . $room->images->first()->image_path) }}" class="w-full h-96 object-cover" alt="Kamar {{ $room->room_number }}">
                    @else
                        <div class="w-full h-96 bg-gray-200 flex items-center justify-center text-gray-400">
                            <i class="fas fa-image text-6xl"></i>
                        </div>
                    @endif
                </div>
                <div class="grid grid-cols-4 gap-4">
                    @foreach($room->images->skip(1) as $image)
                        <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-24 object-cover rounded-lg shadow-sm border border-gray-100" alt="Room thumbnail">
                    @endforeach
                </div>
            </div>

            <!-- Detail Info -->
            <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">Kamar {{ $room->room_number }}</h1>
                        <p class="text-gray-500 text-lg"><i class="fas fa-building mr-2"></i>{{ $room->branch->name }}</p>
                    </div>
                    <span class="bg-blue-100 text-blue-700 px-4 py-2 rounded-full font-bold uppercase tracking-wide text-sm">
                        {{ $room->type }}
                    </span>
                </div>

                <div class="border-y border-gray-100 py-6 mb-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-400 uppercase font-bold mb-1">Harga Sewa</p>
                        <p class="text-3xl font-extrabold text-blue-600">Rp {{ number_format($room->price, 0, ',', '.') }}<span class="text-lg font-normal text-gray-400">/bulan</span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-400 uppercase font-bold mb-1">Status</p>
                        <span class="text-green-600 font-bold"><i class="fas fa-check-circle mr-1"></i>Tersedia</span>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="font-bold text-lg mb-4">Fasilitas Kamar</h3>
                    <div class="grid grid-cols-2 gap-y-3">
                        @foreach($room->facilities as $facility)
                            <div class="flex items-center text-gray-700">
                                <i class="{{ $facility->icon ?: 'fas fa-check' }} w-8 text-blue-500"></i>
                                <span>{{ $facility->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="font-bold text-lg mb-2">Deskripsi</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $room->description ?: 'Tidak ada deskripsi.' }}</p>
                </div>

                <button class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold text-lg hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Booking Kamar Sekarang
                </button>
            </div>
        </div>
    </div>
</body>
</html>
