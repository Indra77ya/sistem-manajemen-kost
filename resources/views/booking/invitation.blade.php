<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Kamar - KostKita</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto mt-10 mb-20 px-4">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 p-6 text-white text-center">
                <h1 class="text-2xl font-bold">Lengkapi Data Pemesanan</h1>
                <p class="mt-2 text-blue-100">Kamar {{ $invitation->room->number }} - {{ $invitation->room->branch->name }}</p>
            </div>

            <div class="p-8">
                <form action="{{ route('booking.invitation.store', $invitation->token) }}" method="POST">
                    @csrf

                    <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Data Diri</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi (Untuk Login Nanti)</label>
                            <input type="password" name="password" required
                                   class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Detail Sewa</h2>

                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rencana Tanggal Masuk</label>
                        <input type="date" name="check_in_date" value="{{ old('check_in_date') }}" required min="{{ date('Y-m-d') }}"
                               class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('check_in_date') border-red-500 @enderror">
                        @error('check_in_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-gray-50 p-6 rounded-md mb-8 border border-gray-200">
                        <div class="flex justify-between items-center text-gray-700 mb-2">
                            <span>Harga Sewa Bulanan:</span>
                            <span class="font-bold">Rp {{ number_format($invitation->room->price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-blue-700 text-lg">
                            <span>Biaya Booking (DP):</span>
                            <span class="font-extrabold">Rp {{ number_format($invitation->room->branch->default_booking_fee, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-4 italic">
                            *Dengan melanjutkan, Anda setuju untuk membayar DP sesuai nominal di atas untuk mengamankan kamar Anda.
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white px-8 py-4 rounded-md font-bold text-lg hover:bg-blue-700 transition duration-300 shadow-lg">
                        Konfirmasi & Pesan Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
