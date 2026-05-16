<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { text-align: center; margin-bottom: 5px; }
        .meta { text-align: center; color: #666; margin-bottom: 15px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 5px 8px; }
        th { background: #f0f0f0; font-weight: bold; text-align: center; }
        td { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background: #f9f9f9; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <p class="meta">Dicetak: {{ $tanggal }}</p>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="14%">No. Surat Jalan</th>
                <th width="12%">Kode Bahan</th>
                <th width="16%">Nama Bahan</th>
                <th width="12%">Supplier</th>
                <th width="8%">Qty</th>
                <th width="6%">Satuan</th>
                <th width="12%">Harga/Yard</th>
                <th width="16%">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; $totalHarga = 0; @endphp
            @forelse($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->no_surat_jalan }}</td>
                    <td>{{ $item->kode_bahan }}</td>
                    <td>{{ $item->nama_bahan }}</td>
                    <td>{{ $item->supplier }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $item->satuan ?? 'yard' }}</td>
                    <td class="text-right">{{ number_format($item->rp_per_yard, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->total_harga, 0, ',', '.') }}</td>
                </tr>
                @php $totalQty += $item->quantity; $totalHarga += $item->total_harga; @endphp
            @empty
                <tr><td colspan="9" class="text-center">Tidak ada data</td></tr>
            @endforelse
            @if($data->count())
                <tr class="total-row">
                    <td colspan="5" class="text-center">TOTAL</td>
                    <td class="text-right">{{ number_format($totalQty, 2, ',', '.') }}</td>
                    <td></td>
                    <td></td>
                    <td class="text-right">{{ number_format($totalHarga, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
