<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Booking - KostKita</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto mt-10 px-4">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Konfirmasi Booking Kamar {{ $room->number }}</h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Detail Kamar</h2>
                        <p class="text-gray-600">Cabang: {{ $room->branch->name }}</p>
                        <p class="text-gray-600">Tipe: {{ $room->type }}</p>
                        <p class="text-gray-600">Harga: Rp {{ number_format($room->price, 0, ',', '.') }}/bulan</p>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700 mb-2">Biaya Booking</h2>
                        <p class="text-3xl font-bold text-blue-600">Rp {{ number_format($room->branch->default_booking_fee, 0, ',', '.') }}</p>
                        <p class="text-sm text-gray-500 mt-1">*Uang muka ini akan dikonversi menjadi Deposit saat Anda masuk.</p>
                    </div>
                </div>

                <form action="{{ route('booking.store', $room) }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rencana Tanggal Masuk</label>
                        <input type="date" name="check_in_date" required min="{{ date('Y-m-d') }}"
                               class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('check_in_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between mt-8">
                        <a href="/" class="text-gray-600 hover:text-gray-800">Kembali</a>
                        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-md font-bold hover:bg-blue-700 transition duration-300">
                            Konfirmasi & Pesan Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
