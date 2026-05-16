<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 5px; }
        .meta { text-align: center; color: #666; margin-bottom: 15px; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 6px; }
        th { background: #f0f0f0; font-weight: bold; text-align: center; }
        td { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background: #f9f9f9; }
        .badge-gudang { background: #dbeafe; color: #1d4ed8; padding: 2px 6px; border-radius: 4px; font-size: 9px; }
        .badge-garmen { background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 4px; font-size: 9px; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <p class="meta">Dicetak: {{ $tanggal }}</p>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="9%">Tanggal</th>
                <th width="12%">No. Surat Jalan</th>
                <th width="11%">Kode Bahan</th>
                <th width="16%">Nama Bahan</th>
                <th width="12%">Supplier</th>
                <th width="7%">Qty</th>
                <th width="5%">Satuan</th>
                <th width="10%">Harga/Yard</th>
                <th width="10%">Total</th>
                <th width="5%">Lokasi</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; $totalHarga = 0; @endphp
            @forelse($data as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $item->tanggal ? $item->tanggal->format('d/m/Y') : '—' }}</td>
                    <td>{{ $item->no_surat_jalan }}</td>
                    <td>{{ $item->kode_bahan }}</td>
                    <td>{{ $item->nama_bahan }}</td>
                    <td>{{ $item->supplier }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $item->satuan ?? 'yard' }}</td>
                    <td class="text-right">{{ number_format($item->rp_per_yard, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->total_harga, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="{{ $item->lokasi === 'Gudang' ? 'badge-gudang' : 'badge-garmen' }}">{{ $item->lokasi }}</span>
                    </td>
                </tr>
                @php $totalQty += $item->quantity; $totalHarga += $item->total_harga; @endphp
            @empty
                <tr><td colspan="11" class="text-center">Tidak ada data</td></tr>
            @endforelse
            @if($data->count())
                <tr class="total-row">
                    <td colspan="6" class="text-center">TOTAL</td>
                    <td class="text-right">{{ number_format($totalQty, 2, ',', '.') }}</td>
                    <td></td>
                    <td></td>
                    <td class="text-right">{{ number_format($totalHarga, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
