<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Transaction;
use Carbon\Carbon;

class CatatanKeuanganController extends Controller
{
    public function pemasukan(Request $request)
    {
        $query = Transaction::with('student')
            ->where('type', 'deposit')
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('nisn', 'ilike', "%{$search}%");
            });
        }

        $transactions = $query->paginate(10)->withQueryString();

        return Inertia::render('CatatanKeuangan/Pemasukan', [
            'transactions' => $transactions,
            'filters' => $request->only(['search'])
        ]);
    }

    public function pengeluaran(Request $request)
    {
        $query = Transaction::with('student')
            ->where('type', 'withdrawal')
            ->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('nisn', 'ilike', "%{$search}%");
            });
        }

        $transactions = $query->paginate(10)->withQueryString();

        return Inertia::render('CatatanKeuangan/Pengeluaran', [
            'transactions' => $transactions,
            'filters' => $request->only(['search'])
        ]);
    }

    public function laporan(Request $request)
    {
        // Default to current month
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $cycleLabel = 'Bulan Ini (' . $startDate->translatedFormat('F Y') . ')';

        if ($request->has('cycle')) {
            $cycle = $request->cycle;
            if ($cycle === 'last_month') {
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                $cycleLabel = 'Bulan Lalu (' . $startDate->translatedFormat('F Y') . ')';
            } elseif ($cycle === 'last_3_months') {
                $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $cycleLabel = '3 Bulan Terakhir';
            } elseif ($cycle === 'custom' && $request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                $cycleLabel = $startDate->translatedFormat('d M Y') . ' - ' . $endDate->translatedFormat('d M Y');
            }
        }

        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])->get();

        $totalPemasukan = $transactions->where('type', 'deposit')->sum('amount');
        $totalPengeluaran = $transactions->where('type', 'withdrawal')->sum('amount');
        $selisih = $totalPemasukan - $totalPengeluaran;

        // Group for chart or daily breakdown if needed (optional)
        $dailyData = $transactions->groupBy(function($date) {
            return Carbon::parse($date->created_at)->format('Y-m-d');
        });

        return Inertia::render('CatatanKeuangan/Laporan', [
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'selisih' => $selisih,
            'cycleLabel' => $cycleLabel,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'currentCycle' => $request->cycle ?? 'this_month'
        ]);
    }

    private function getFilterDates(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $cycleLabel = 'Bulan Ini (' . $startDate->translatedFormat('F Y') . ')';

        if ($request->has('cycle')) {
            $cycle = $request->cycle;
            if ($cycle === 'last_month') {
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                $cycleLabel = 'Bulan Lalu (' . $startDate->translatedFormat('F Y') . ')';
            } elseif ($cycle === 'last_3_months') {
                $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $cycleLabel = '3 Bulan Terakhir';
            } elseif ($cycle === 'custom' && $request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                $cycleLabel = $startDate->translatedFormat('d M Y') . ' - ' . $endDate->translatedFormat('d M Y');
            }
        }
        return [$startDate, $endDate, $cycleLabel];
    }

    private function generateExportData(Request $request, $type = null)
    {
        list($startDate, $endDate, $cycleLabel) = $this->getFilterDates($request);
        $query = Transaction::with('student')->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at', 'asc');
        
        if ($type === 'deposit') {
            $query->where('type', 'deposit');
            $title = 'Laporan Pemasukan (Setoran Tunai)';
        } elseif ($type === 'withdrawal') {
            $query->where('type', 'withdrawal');
            $title = 'Laporan Pengeluaran (Penarikan Tunai)';
        } else {
            $title = 'Laporan Keuangan Bank Mini';
        }

        $transactions = $query->get();
        return compact('transactions', 'title', 'cycleLabel', 'type', 'startDate', 'endDate');
    }

    public function exportLaporanPdf(Request $request)
    {
        $data = $this->generateExportData($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan_transaksi', $data)->setPaper('a4', 'landscape');
        return $pdf->download('laporan_keuangan_bank_mini.pdf');
    }

    public function exportLaporanExcel(Request $request)
    {
        $data = $this->generateExportData($request);
        return \Response::make(view('pdf.laporan_transaksi_excel', $data), 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="laporan_keuangan_bank_mini.xls"'
        ]);
    }

    public function exportPemasukanPdf(Request $request)
    {
        $data = $this->generateExportData($request, 'deposit');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan_transaksi', $data)->setPaper('a4', 'landscape');
        return $pdf->download('laporan_pemasukan_bank_mini.pdf');
    }

    public function exportPemasukanExcel(Request $request)
    {
        $data = $this->generateExportData($request, 'deposit');
        return \Response::make(view('pdf.laporan_transaksi_excel', $data), 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="laporan_pemasukan_bank_mini.xls"'
        ]);
    }

    public function exportPengeluaranPdf(Request $request)
    {
        $data = $this->generateExportData($request, 'withdrawal');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan_transaksi', $data)->setPaper('a4', 'landscape');
        return $pdf->download('laporan_pengeluaran_bank_mini.pdf');
    }

    public function exportPengeluaranExcel(Request $request)
    {
        $data = $this->generateExportData($request, 'withdrawal');
        return \Response::make(view('pdf.laporan_transaksi_excel', $data), 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="laporan_pengeluaran_bank_mini.xls"'
        ]);
    }
}

