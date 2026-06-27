<table>
    <tr>
        <th colspan="6" style="text-align: center; font-weight: bold; font-size: 16px;">{{ $title }}</th>
    </tr>
    <tr>
        <th colspan="6" style="text-align: center;">Aplikasi Bank Mini Sekolah</th>
    </tr>
    <tr>
        <th colspan="6" style="text-align: center;">Periode: {{ $cycleLabel }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #0f7632; color: #ffffff;">No</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #0f7632; color: #ffffff;">Tanggal</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #0f7632; color: #ffffff;">No. Transaksi</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #0f7632; color: #ffffff;">Nasabah / Siswa</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #0f7632; color: #ffffff;">Jenis Transaksi</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #0f7632; color: #ffffff;">Nominal (Rp)</th>
    </tr>
    @php 
        $totalPemasukan = 0;
        $totalPengeluaran = 0;
    @endphp
    @forelse($transactions as $index => $tx)
        @php
            if($tx->type == 'deposit') $totalPemasukan += $tx->amount;
            if($tx->type == 'withdrawal') $totalPengeluaran += $tx->amount;
        @endphp
        <tr>
            <td style="border: 1px solid #000;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000;">{{ \Carbon\Carbon::parse($tx->created_at)->format('Y-m-d H:i') }}</td>
            <td style="border: 1px solid #000;">{{ $tx->transaction_number ?? '-' }}</td>
            <td style="border: 1px solid #000;">{{ $tx->student ? $tx->student->name : 'Nasabah Umum' }}</td>
            <td style="border: 1px solid #000;">{{ $tx->type == 'deposit' ? 'Setoran Tunai' : 'Penarikan Tunai' }}</td>
            <td style="border: 1px solid #000;">{{ number_format($tx->amount, 0, ',', '.') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6" style="border: 1px solid #000; text-align: center;">Tidak ada transaksi pada periode ini.</td>
        </tr>
    @endforelse
    
    <tr></tr>
    @if(!$type || $type == 'deposit')
    <tr>
        <td colspan="5" style="text-align: right; font-weight: bold;">Total Pemasukan:</td>
        <td style="font-weight: bold;">{{ number_format($totalPemasukan, 0, ',', '.') }}</td>
    </tr>
    @endif
    @if(!$type || $type == 'withdrawal')
    <tr>
        <td colspan="5" style="text-align: right; font-weight: bold;">Total Pengeluaran:</td>
        <td style="font-weight: bold;">{{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
    </tr>
    @endif
    @if(!$type)
    <tr>
        <td colspan="5" style="text-align: right; font-weight: bold;">Saldo Bersih:</td>
        <td style="font-weight: bold;">{{ number_format($totalPemasukan - $totalPengeluaran, 0, ',', '.') }}</td>
    </tr>
    @endif
</table>
