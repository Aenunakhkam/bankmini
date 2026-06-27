<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function classrooms()
    {
        $academicYearId = session('academic_year_id');

        // Ambil semua kelas beserta jumlah siswa dan hitung tagihan
        $classrooms = Classroom::with(['major'])->withCount([
            'students',
            'billings as total_billings' => function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            },
            'billings as paid_billings' => function ($query) use ($academicYearId) {
                $query->where('is_paid', true)->where('academic_year_id', $academicYearId);
            }
        ])->get();

        // Hitung persentase pembayaran per kelas
        $classrooms->map(function ($classroom) {
            if ($classroom->total_billings > 0) {
                $classroom->payment_percentage = round(($classroom->paid_billings / $classroom->total_billings) * 100, 1);
            } else {
                $classroom->payment_percentage = 0;
            }
            return $classroom;
        });

        return Inertia::render('Reports/Classrooms', [
            'classrooms' => $classrooms
        ]);
    }

    public function classroomDetail($id)
    {
        $classroom = Classroom::with(['major'])->findOrFail($id);
        $academicYearId = session('academic_year_id');

        $students = $classroom->students()->with([
            'billings' => function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId)->with(['category', 'academicYear']);
            }
        ])->withCount([
            'billings as total_billings' => function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            },
            'billings as paid_billings' => function ($query) use ($academicYearId) {
                $query->where('is_paid', true)->where('academic_year_id', $academicYearId);
            }
        ])->get();

        $students->map(function ($student) {
            if ($student->total_billings > 0) {
                $student->payment_percentage = round(($student->paid_billings / $student->total_billings) * 100, 1);
            } else {
                $student->payment_percentage = 0;
            }
            return $student;
        });

        // Hitung rata-rata persentase kelas ini
        $totalBillings = $students->sum('total_billings');
        $paidBillings = $students->sum('paid_billings');
        $classroomPercentage = $totalBillings > 0 ? round(($paidBillings / $totalBillings) * 100, 1) : 0;

        return Inertia::render('Reports/ClassroomDetail', [
            'classroom' => $classroom,
            'students' => $students,
            'stats' => [
                'total_students' => $students->count(),
                'percentage' => $classroomPercentage,
                'total_billings' => $totalBillings,
                'paid_billings' => $paidBillings,
            ]
        ]);
    }

    public function printClassroomPdf($id)
    {
        $classroom = Classroom::with(['major'])->findOrFail($id);
        $academicYearId = session('academic_year_id');

        $students = $classroom->students()->with([
            'billings' => function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId)->with(['category']);
            }
        ])->get();

        $students->map(function ($student) {
            $student->total_billings = $student->billings->sum('amount');
            $student->paid_billings = $student->billings->where('is_paid', true)->sum('amount');
            return $student;
        });

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.classroom_report', compact('classroom', 'students'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->download("laporan_kelas_{$classroom->level}_{$classroom->name}.pdf");
    }

    public function printStudentPdf($id)
    {
        $student = \App\Models\Student::with(['classroom.major'])->findOrFail($id);
        $academicYearId = session('academic_year_id');

        $billings = $student->billings()
            ->where('academic_year_id', $academicYearId)
            ->with(['category', 'paymentDetails.payment'])
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.student_report', compact('student', 'billings'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->download("laporan_keuangan_{$student->name}.pdf");
    }

    public function printBkuPdf()
    {
        $academicYearId = session('academic_year_id');
        $academicYear = \App\Models\AcademicYear::find($academicYearId);
        
        // Fetch all bank transactions ordered by date ascending
        $rawTransactions = \App\Models\Transaction::with(['student', 'user'])
            ->orderBy('created_at', 'asc')
            ->get();
            
        $transactions = collect();
        $runningBalance = 0;
        
        foreach($rawTransactions as $tx) {
            $debit = 0;
            $kredit = 0;
            
            // Di Bank, deposit (setoran uang ke bank) artinya Kas Bank (atau tabungan nasabah) bertambah (Kredit)
            // Withdrawal (penarikan uang) artinya Kas Bank / tabungan nasabah berkurang (Debit)
            if ($tx->type == 'deposit') {
                $kredit = $tx->amount;
                $runningBalance += $tx->amount;
            } else {
                $debit = $tx->amount;
                $runningBalance -= $tx->amount;
            }

            $keterangan = $tx->type == 'deposit' ? 'Setoran Tunai' : 'Penarikan Tunai';
            if ($tx->student) {
                $nasabah = $tx->student->name;
            } else {
                $nasabah = 'Nasabah Umum';
            }

            $transactions->push((object)[
                'date' => $tx->created_at,
                'no_bukti' => $tx->transaction_number ?? '-',
                'nasabah' => $nasabah,
                'keterangan' => $keterangan,
                'debit' => $debit,
                'kredit' => $kredit,
                'saldo' => $runningBalance
            ]);
        }
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.bku_report', compact('transactions', 'academicYear'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->stream("Buku_Mutasi_Bank_" . str_replace('/', '_', $academicYear ? $academicYear->name : 'All') . ".pdf");
    }
}
