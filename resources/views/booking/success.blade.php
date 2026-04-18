<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Berhasil - KostKita</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-2xl mx-auto mt-20 px-4 text-center">
        <div class="bg-white p-10 rounded-lg shadow-lg">
            <div class="mb-6 text-green-500">
                <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Booking Berhasil Diajukan!</h1>
            <p class="text-gray-600 mb-8">
                Terima kasih telah memesan Kamar {{ $booking->room->number }} di {{ $booking->branch->name }}.
                Pesanan Anda telah kami terima dan sedang menunggu pembayaran.
            </p>

            <div class="bg-blue-50 p-6 rounded-md mb-8 text-left">
                <h2 class="font-bold text-blue-800 mb-2 text-lg">Instruksi Pembayaran:</h2>
                <p class="text-blue-700">Silakan transfer sebesar:</p>
                <p class="text-3xl font-bold text-blue-900 mb-4">Rp {{ number_format($booking->booking_fee, 0, ',', '.') }}</p>
                <p class="text-blue-700 text-sm italic">
                    Harap selesaikan pembayaran sebelum: <br>
                    <span class="font-bold">{{ $booking->expires_at->format('d M Y, H:i') }} WIB</span>
                </p>
            </div>

            <div class="flex flex-col gap-4">
                <a href="/admin/bookings" class="bg-blue-600 text-white px-8 py-3 rounded-md font-bold hover:bg-blue-700 transition duration-300">
                    Buka Dashboard & Unggah Bukti Bayar
                </a>
                <a href="/" class="text-gray-600 hover:text-gray-800 underline">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>
