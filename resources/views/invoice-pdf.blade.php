<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; }
        .invoice-box { max-width: 800px; margin: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f5f5f5; }
        .total { font-weight: bold; text-align: right; }
        .footer { margin-top: 50px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>INVOICE</h1>
            <p>{{ $invoice->branch->name }}</p>
            <p>{{ $invoice->branch->address }} | {{ $invoice->branch->phone }}</p>
        </div>

        <div style="margin-bottom: 20px;">
            <table style="border: none;">
                <tr style="border: none;">
                    <td style="border: none; padding-left: 0;">
                        <strong>Kepada:</strong><br>
                        {{ $invoice->lease->tenant->name }}<br>
                        Kamar: {{ $invoice->lease->room->number }}
                    </td>
                    <td style="border: none; text-align: right; padding-right: 0;">
                        <strong>Nomor:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>Tanggal:</strong> {{ $invoice->created_at->format('d/m/Y') }}<br>
                        <strong>Jatuh Tempo:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                    </td>
                </tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th style="text-align: right;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td style="text-align: right;">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="total">TOTAL</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 20px;">
            <strong>Status:</strong>
            <span style="color: {{ $invoice->status === 'paid' ? 'green' : 'red' }}">
                {{ strtoupper($invoice->status) }}
            </span>
        </div>

        <div style="margin-top: 30px; padding: 15px; border: 1px dashed #ccc; background-color: #fafafa;">
            <strong>Instruksi Pembayaran:</strong>
            <p style="font-size: 12px; margin-bottom: 5px;">Silakan melakukan pembayaran melalui salah satu metode berikut:</p>
            <table style="border: none; font-size: 12px;">
                @foreach(\App\Models\PaymentMethod::where('is_active', true)->get() as $method)
                <tr style="border: none;">
                    <td style="border: none; padding: 2px 0; width: 120px;"><strong>{{ $method->name }}</strong></td>
                    <td style="border: none; padding: 2px 0;">: {{ $method->account_number }} (a.n. {{ $method->account_holder }})</td>
                </tr>
                @endforeach
            </table>
            @if(\App\Models\PaymentMethod::where('is_active', true)->whereNotNull('description')->exists())
                <p style="font-size: 11px; margin-top: 5px; color: #555;">
                    * {{ \App\Models\PaymentMethod::where('is_active', true)->whereNotNull('description')->first()->description }}
                </p>
            @endif
        </div>

        <div class="footer">
            <p>* Harap simpan bukti pembayaran ini.<br>
            * Jika ada pertanyaan, hubungi pengelola cabang terkait.</p>
        </div>
    </div>
</body>
</html>
