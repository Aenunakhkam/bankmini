<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        if ($perPage === 'all') {
            $perPage = 999999;
        }

        $transactionsQuery = Transaction::with('student')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('transaction_number', 'like', "%{$search}%")
                      ->orWhereHas('student', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%")
                             ->orWhere('nisn', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('created_at', 'desc');

        $transactions = $transactionsQuery->paginate($perPage)->withQueryString();

        // Get voided transactions to mark them
        $voidedTransactionNumbers = Transaction::where('description', 'like', 'Koreksi Pembatalan: %')
            ->pluck('description')
            ->map(function ($desc) {
                return str_replace('Koreksi Pembatalan: ', '', $desc);
            })->toArray();

        foreach ($transactions->items() as $trx) {
            $trx->is_voided = in_array($trx->transaction_number, $voidedTransactionNumbers);
        }

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => [
                'search' => $search,
                'per_page' => $request->input('per_page', 10)
            ]
        ]);
    }
}
