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
                <th width="16%">No. Surat Jalan</th>
                <th width="12%">Tgl Kirim</th>
                <th width="14%">Kode Bahan</th>
                <th width="22%">Nama Bahan</th>
                <th width="16%">Supplier</th>
                <th width="10%">Qty</th>
                <th width="6%">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; @endphp
            @forelse($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->no_surat_jalan }}</td>
                    <td class="text-center">{{ $item->tanggal_kirim }}</td>
                    <td>{{ $item->kode_bahan }}</td>
                    <td>{{ $item->nama_bahan }}</td>
                    <td>{{ $item->supplier }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $item->satuan ?? 'yard' }}</td>
                </tr>
                @php $totalQty += $item->quantity; @endphp
            @empty
                <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
            @endforelse
            @if($data->count())
                <tr class="total-row">
                    <td colspan="6" class="text-center">TOTAL</td>
                    <td class="text-right">{{ number_format($totalQty, 2, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
