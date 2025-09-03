<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $req)
    {
        $q      = trim((string) $req->q);
        $status = $req->status;                // Processing|Shipped|Arrived|Completed|Cancelled
        $from   = $req->from;                  // YYYY-MM-DD
        $to     = $req->to;                    // YYYY-MM-DD

        $orders = Order::with(['user','items.book','shipment'])
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($from,   fn($qq) => $qq->whereDate('order_date', '>=', $from))
            ->when($to,     fn($qq) => $qq->whereDate('order_date', '<=', $to))
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('id', $q)
                      ->orWhereHas('user', fn($u) => $u->where('name','like',"%{$q}%")
                                                       ->orWhere('email','like',"%{$q}%"));
                });
            })
            ->orderByDesc('order_date')
            ->paginate(20)
            ->withQueryString();

        return view('staff.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user','items.book','shipment','transactions']);
        return view('staff.orders.show', compact('order'));
    }
}
