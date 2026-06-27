<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return Inertia::render('Settings/Index', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'admin_fee_active' => 'required|boolean',
            'admin_fee_amount' => 'required|numeric|min:0',
        ]);

        Setting::updateOrCreate(['key' => 'admin_fee_active'], ['value' => $request->admin_fee_active]);
        Setting::updateOrCreate(['key' => 'admin_fee_amount'], ['value' => $request->admin_fee_amount]);

        return back()->with('message', 'Pengaturan berhasil disimpan.');
    }

    public function applyAdminFee()
    {
        $isActive = Setting::where('key', 'admin_fee_active')->value('value');
        $amount = Setting::where('key', 'admin_fee_amount')->value('value');

        if (!$isActive || $amount <= 0) {
            return back()->with('error', 'Fitur Potongan Admin sedang dinonaktifkan atau nominal belum diset.');
        }

        $currentMonth = Carbon::now()->format('m-Y');
        $description = "Potongan Biaya Admin - " . Carbon::now()->isoFormat('MMMM Y');

        // Check if we already applied this month to prevent double deduction
        // We look for a recent transaction with this exact description in this month
        $alreadyApplied = Transaction::where('description', $description)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->exists();

        if ($alreadyApplied) {
            return back()->with('error', 'Potongan admin untuk bulan ini sudah pernah dilakukan!');
        }

        $students = Student::whereIn('status', ['Aktif', 'active'])->get();
        $count = 0;

        foreach ($students as $student) {
            $currentBalance = Transaction::where('student_id', $student->id)
                ->selectRaw("SUM(CASE WHEN type = 'deposit' THEN amount ELSE -amount END) as balance")
                ->value('balance') ?? 0;

            if ($currentBalance >= $amount) {
                Transaction::create([
                    'transaction_number' => 'ADM-' . strtoupper(Str::random(6)),
                    'student_id' => $student->id,
                    'user_id' => auth()->id(),
                    'type' => 'withdrawal',
                    'amount' => $amount,
                    'balance_before' => $currentBalance,
                    'balance_after' => $currentBalance - $amount,
                    'description' => $description
                ]);
                $count++;
            }
        }

        return back()->with('message', "Berhasil memotong saldo $count nasabah sebesar Rp" . number_format($amount, 0, ',', '.') . ".");
    }
}
