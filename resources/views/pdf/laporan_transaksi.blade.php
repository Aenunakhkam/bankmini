<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 40px 40px 60px 40px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #000;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            color: #000;
            font-size: 12px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: left;
        }
        .table th {
            background-color: #eee;
            color: #000;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .summary-box {
            margin-top: 20px;
            width: 300px;
            float: right;
            border: 1px solid #000;
            padding: 10px;
            background-color: #fff;
        }
        .summary-box table {
            width: 100%;
        }
        .summary-box td {
            padding: 3px 0;
        }
        .summary-box .total {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .text-bold { font-weight: bold; }
        footer {
            position: fixed; 
            bottom: -40px; 
            left: 0px; 
            right: 0px;
            height: 30px; 
            font-size: 10px;
            color: #000;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .pagenum:before {
            content: counter(page);
        }
    </style>
</head>
<body>
    <footer>
        <table width="100%" style="border: none;">
            <tr>
                <td style="border: none; text-align: left;">Dicetak oleh Sistem Bank Mini ({{ \Carbon\Carbon::now()->format('d/m/Y H:i') }})</td>
                <td style="border: none; text-align: right;">Halaman <span class="pagenum"></span></td>
            </tr>
        </table>
    </footer>

    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Aplikasi Bank Mini Sekolah</p>
        <p>Periode: {{ $cycleLabel }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="20%">No. Transaksi</th>
                <th width="25%">Nasabah / Siswa</th>
                <th width="15%">Jenis Transaksi</th>
                <th width="20%">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
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
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($tx->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="text-center">{{ $tx->transaction_number ?? '-' }}</td>
                    <td>{{ $tx->student ? $tx->student->name : 'Nasabah Umum' }}</td>
                    <td class="text-center text-bold">
                        {{ $tx->type == 'deposit' ? 'Setoran Tunai' : 'Penarikan Tunai' }}
                    </td>
                    <td class="text-right">{{ number_format($tx->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="clearfix">
        <div class="summary-box">
            <table>
                @if(!$type || $type == 'deposit')
                <tr>
                    <td>Total Pemasukan:</td>
                    <td class="text-right">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if(!$type || $type == 'withdrawal')
                <tr>
                    <td>Total Pengeluaran:</td>
                    <td class="text-right">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if(!$type)
                <tr class="total">
                    <td>Saldo Bersih:</td>
                    <td class="text-right text-bold">Rp {{ number_format($totalPemasukan - $totalPengeluaran, 0, ',', '.') }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>
</body>
</html>
