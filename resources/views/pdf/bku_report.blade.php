<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Kas Umum - {{ $academicYear ? $academicYear->name : 'Semua Tahun' }}</title>
    <style>
        @page { margin: 20px; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; margin: 0; color: #000; }
        .kop-surat { width: 100%; border-bottom: 4px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-surat td { border: none; padding: 0; }
        .kop-surat .logo { width: 80px; text-align: left; }
        .kop-surat .teks { text-align: center; }
        .kop-surat h1 { margin: 0; font-size: 22px; text-transform: uppercase; font-weight: bold; }
        .kop-surat h2 { margin: 5px 0 0 0; font-size: 18px; text-transform: uppercase; font-weight: bold; }
        .kop-surat p { margin: 3px 0 0 0; font-size: 12px; }
        
        .title { text-align: center; margin-bottom: 20px; }
        .title h3 { margin: 0; font-size: 14px; text-transform: uppercase; text-decoration: underline; }
        .title p { margin: 5px 0 0 0; font-size: 12px; }
        
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #000; }
        table.data th, table.data td { border: 1px solid #000; padding: 8px 6px; text-align: left; vertical-align: top; }
        table.data th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 11px; text-align: center; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        .footer { margin-top: 50px; width: 100%; border: none; }
        .footer td { border: none; text-align: center; vertical-align: top; width: 50%; }
        .signature-box { margin-top: 80px; font-weight: bold; }
        
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <table class="kop-surat">
        <tr>
            <td class="teks">
                <h1>SMK HASYIM ASYARI BOJONG</h1>
                <p>JL.Babakan Tuwel Bojong Tegal 52465</p>
            </td>
        </tr>
    </table>

    <div class="title">
        <h3>LAPORAN BUKU MUTASI KASIR (BANK)</h3>
        <p>Periode: Keseluruhan</p>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Waktu/Tanggal</th>
                <th width="15%">No. Transaksi</th>
                <th width="20%">Nama Nasabah</th>
                <th width="16%">Keterangan</th>
                <th width="11%">Debit (Keluar)</th>
                <th width="11%">Kredit (Masuk)</th>
                <th width="10%">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1; 
                $totalDebit = 0;
                $totalKredit = 0;
            @endphp
            @forelse($transactions as $tx)
                @php
                    $totalDebit += $tx->debit;
                    $totalKredit += $tx->kredit;
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-center">{{ date('d/m/Y H:i', strtotime($tx->date)) }}</td>
                    <td>{{ $tx->no_bukti }}</td>
                    <td>{{ $tx->nasabah }}</td>
                    <td>{{ $tx->keterangan }}</td>
                    <td class="text-right">{{ $tx->debit > 0 ? number_format($tx->debit, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $tx->kredit > 0 ? number_format($tx->kredit, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ number_format($tx->saldo, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada transaksi perbankan yang tercatat.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL MUTASI</td>
                <td class="text-right">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalKredit, 0, ',', '.') }}</td>
                <td class="text-right">-</td>
            </tr>
        </tfoot>
    </table>

    <table class="footer">
        <tr>
            <td>
                <br>
                Mengetahui,<br>
                Kepala Sekolah
                <div class="signature-box">
                    __________________________<br>
                    NIP. 
                </div>
            </td>
            <td>
                ................., {{ date('d F Y') }}<br>
                Bendahara / Bag. Keuangan
                <div class="signature-box">
                    <strong>{{ auth()->user()->name }}</strong><br>
                    __________________________
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 40px; font-size: 10px; color: #555; font-style: italic; border-top: 1px dashed #ccc; padding-top: 5px;">
        * Dokumen Buku Kas Umum ini dicetak secara otomatis oleh Sistem Manajemen Keuangan (BANKMINI) pada {{ date('d/m/Y H:i:s') }}.
    </div>

</body>
</html>
