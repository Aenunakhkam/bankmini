<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\CashLedger;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TellerController extends Controller
{
    public function index()
    {
        return Inertia::render('Teller/Index');
    }

    public function search(Request $request)
    {
        $accountNumber = $request->account_number;

        $customer = Student::where('nisn', $accountNumber)->first();

        if (!$customer) {
            return response()->json(['error' => 'Nasabah tidak ditemukan'], 404);
        }

        // Return customer data + their recent transactions
        $recentTransactions = Transaction::where('student_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $voidedTransactionNumbers = Transaction::where('student_id', $customer->id)
            ->where('description', 'like', 'Koreksi Pembatalan: %')
            ->pluck('description')
            ->map(function ($desc) {
                return str_replace('Koreksi Pembatalan: ', '', $desc);
            })->toArray();

        foreach ($recentTransactions as $trx) {
            $trx->is_voided = in_array($trx->transaction_number, $voidedTransactionNumbers);
        }

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'account_number' => $customer->nisn,
            'type' => $customer->nasabah_type ?? 'Siswa',
            'balance' => $customer->balance,
            'recent_transactions' => $recentTransactions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:1000'
        ]);

        try {
            DB::beginTransaction();

            $customer = Student::findOrFail($request->student_id);
            $amount = $request->amount;
            $type = $request->type;

            $balanceBefore = $customer->balance;

            if ($type === 'withdrawal' && $balanceBefore < $amount) {
                return back()->with('error', 'Saldo Anda tidak cukup');
            }

            $balanceAfter = $type === 'deposit' ? ($balanceBefore + $amount) : ($balanceBefore - $amount);

            // Update customer balance
            $customer->balance = $balanceAfter;
            $customer->save();

            // Create Transaction Record
            $transaction = Transaction::create([
                'transaction_number' => 'TRX-' . time() . '-' . rand(1000, 9999),
                'student_id' => $customer->id,
                'user_id' => auth()->id(),
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $type === 'deposit' ? 'Setoran Tabungan' : 'Tarik Tunai'
            ]);

            // Sync with Global Cash Ledger
            CashLedger::create([
                'date' => Carbon::today(),
                'type' => $type === 'deposit' ? 'debit' : 'credit', // Debit = cash in, Credit = cash out
                'amount' => $amount,
                'description' => ($type === 'deposit' ? 'Setoran nasabah: ' : 'Penarikan nasabah: ') . $customer->name,
                'reference_type' => Transaction::class,
                'reference_id' => $transaction->id
            ]);

            DB::commit();

            return back()->with('success', 'Transaksi berhasil diproses! Saldo saat ini: Rp ' . number_format($balanceAfter, 0, ',', '.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }

    public function receipt($id)
    {
        $transaction = Transaction::with('student')->findOrFail($id);

        return Inertia::render('Teller/Receipt', [
            'transaction' => $transaction
        ]);
    }

    public function voidTransaction($id)
    {
        try {
            DB::beginTransaction();

            $original = Transaction::findOrFail($id);

            // Check if it's already a void transaction
            if (str_starts_with($original->description, 'Koreksi Pembatalan')) {
                return response()->json(['error' => 'Transaksi ini adalah jurnal pembatalan dan tidak dapat dibatalkan lagi.'], 400);
            }

            // Check if it has already been voided
            $alreadyVoided = Transaction::where('description', 'Koreksi Pembatalan: ' . $original->transaction_number)->exists();
            if ($alreadyVoided) {
                return response()->json(['error' => 'Transaksi ini sudah pernah dibatalkan sebelumnya!'], 400);
            }

            $customer = Student::findOrFail($original->student_id);
            $amount = $original->amount;

            // Determine the reverse type
            $reverseType = $original->type === 'deposit' ? 'withdrawal' : 'deposit';

            $balanceBefore = $customer->balance;

            // If original was deposit, we now withdraw. If original was withdrawal, we now deposit.
            if ($reverseType === 'withdrawal' && $balanceBefore < $amount) {
                return response()->json(['error' => 'Saldo nasabah saat ini tidak mencukupi untuk membatalkan setoran ini.'], 400);
            }

            $balanceAfter = $reverseType === 'deposit' ? ($balanceBefore + $amount) : ($balanceBefore - $amount);

            // Update customer balance
            $customer->balance = $balanceAfter;
            $customer->save();

            // Create Void Transaction Record
            $transaction = Transaction::create([
                'transaction_number' => 'VOID-' . time() . '-' . rand(1000, 9999),
                'student_id' => $customer->id,
                'user_id' => auth()->id(),
                'type' => $reverseType,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => 'Koreksi Pembatalan: ' . $original->transaction_number
            ]);

            // Sync with Global Cash Ledger
            CashLedger::create([
                'date' => Carbon::today(),
                'type' => $reverseType === 'deposit' ? 'debit' : 'credit',
                'amount' => $amount,
                'description' => 'Pembatalan transaksi nasabah: ' . $customer->name,
                'reference_type' => Transaction::class,
                'reference_id' => $transaction->id
            ]);

            DB::commit();

            return response()->json(['message' => 'Transaksi berhasil dibatalkan (di-void)!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal membatalkan transaksi: ' . $e->getMessage()], 500);
        }
    }

    public function destroyTransaction($id)
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($id);
            $customer = Student::findOrFail($transaction->student_id);

            // Revert the balance
            if ($transaction->type === 'deposit') {
                $customer->balance -= $transaction->amount;
            } else {
                $customer->balance += $transaction->amount;
            }
            $customer->save();

            // Delete associated cash ledger
            CashLedger::where('reference_type', Transaction::class)
                ->where('reference_id', $transaction->id)
                ->delete();

            // Delete transaction
            $transaction->delete();

            DB::commit();
            return response()->json(['message' => 'Transaksi berhasil dihapus secara permanen.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menghapus transaksi: ' . $e->getMessage()], 500);
        }
    }

    public function updateTransaction(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000'
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($id);
            $customer = Student::findOrFail($transaction->student_id);

            $oldAmount = $transaction->amount;
            $newAmount = $request->amount;

            // Calculate the difference that needs to be applied
            // If it's a deposit and amount increases, difference is positive.
            // If it's a withdrawal and amount increases, it means we took more money out, so difference to balance is negative.
            if ($transaction->type === 'deposit') {
                $difference = $newAmount - $oldAmount;
            } else {
                $difference = $oldAmount - $newAmount; // e.g. old withdrawal 10, new 15. Diff = -5
            }

            // Check if the new difference would cause the customer's current balance to go negative
            if ($customer->balance + $difference < 0) {
                return response()->json(['error' => 'Perubahan ini akan menyebabkan saldo nasabah menjadi minus.'], 400);
            }

            // Update this transaction
            $transaction->amount = $newAmount;
            $transaction->balance_after = $transaction->balance_before + ($transaction->type === 'deposit' ? $newAmount : -$newAmount);
            $transaction->save();

            // Update all subsequent transactions for this student
            $subsequentTransactions = Transaction::where('student_id', $customer->id)
                ->where('created_at', '>', $transaction->created_at)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($subsequentTransactions as $subTrx) {
                $subTrx->balance_before += $difference;
                $subTrx->balance_after += $difference;
                $subTrx->save();
            }

            // Update the cash ledger
            $cashLedger = CashLedger::where('reference_type', Transaction::class)
                ->where('reference_id', $transaction->id)
                ->first();

            if ($cashLedger) {
                $cashLedger->amount = $newAmount;
                $cashLedger->save();
            }

            // Update the customer's total balance
            $customer->balance += $difference;
            $customer->save();

            DB::commit();
            return response()->json(['message' => 'Transaksi berhasil diperbarui dan saldo telah disesuaikan.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal memperbarui transaksi: ' . $e->getMessage()], 500);
        }
    }

    public function resetStudent($id)
    {
        try {
            DB::beginTransaction();

            $customer = Student::findOrFail($id);

            // Get all transaction IDs for this student
            $transactionIds = Transaction::where('student_id', $customer->id)->pluck('id');

            // Delete associated cash ledgers
            CashLedger::where('reference_type', Transaction::class)
                ->whereIn('reference_id', $transactionIds)
                ->delete();

            // Delete all transactions
            Transaction::where('student_id', $customer->id)->delete();

            // Reset student balance to 0
            $customer->balance = 0;
            $customer->save();

            DB::commit();
            return response()->json(['message' => 'Seluruh saldo dan riwayat transaksi nasabah berhasil dihapus bersih.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menghapus data nasabah: ' . $e->getMessage()], 500);
        }
    }
}
