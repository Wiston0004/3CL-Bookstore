<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\TransactionHistory;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $req)
    {
        $q     = trim((string)$req->input('q'));
        $type  = $req->input('type'); // Payment | Refund
        $from  = $req->input('from'); // YYYY-MM-DD
        $to    = $req->input('to');   // YYYY-MM-DD

        $tx = TransactionHistory::with(['order.user'])
            ->when($type, fn($qq) => $qq->where('transaction_type', $type))
            ->when($from, fn($qq) => $qq->whereDate('transaction_date', '>=', $from))
            ->when($to,   fn($qq) => $qq->whereDate('transaction_date', '<=', $to))
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('id', $q)
                      ->orWhere('order_id', $q)
                      ->orWhereHas('order.user', function ($u) use ($q) {
                          $u->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                      });
                });
            })
            ->orderByDesc('transaction_date')
            ->paginate(20)
            ->withQueryString();

        // Simple top-line stats
        $stats = [
            'count'    => TransactionHistory::count(),
            'sum'      => (float) TransactionHistory::sum('amount'),
            'payments' => (float) TransactionHistory::where('transaction_type','Payment')->sum('amount'),
            'refunds'  => (float) TransactionHistory::where('transaction_type','Refund')->sum('amount'),
        ];

        return view('manager.transactions.index', compact('tx','stats'));
    }

    public function show(TransactionHistory $tx)
    {
        $tx->load(['order.items.book','order.user','order.shipment','order.transactions']);
        return view('manager.transactions.show', compact('tx'));
    }
}
