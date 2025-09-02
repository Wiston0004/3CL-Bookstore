<?php

namespace App\Http\Controllers;

use App\Models\{Order, OrderItem, CartItem, Shipment, TransactionHistory, Book};
use App\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{
    // Customer: list their orders
    public function index()
    {
        $orders = Order::with('items.book')
            ->where('user_id', auth()->id())
            ->orderByDesc('order_date')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function updateAddress(Request $request, Order $order)
{
    $this->authorizeAdminOrOwner($order);
    if ($order->status !== Order::STATUS_PROCESSING) {
        return back()->with('err', 'You can only change the address while the order is Processing.');
    }

    $data = $request->validate([
        'shipping_address' => ['required','string','max:2000'],
    ]);

    $order->load('shipment');
    if ($order->shipment) {
        $order->shipment->update(['shipping_address' => $data['shipping_address']]);
    } else {
        // safety: create if absent
        Shipment::create([
            'order_id' => $order->id,
            'shipping_address' => $data['shipping_address'],
        ]);
    }

    return back()->with('ok', 'Shipping address updated.');
}

    // Customer: show one order
    public function show(Order $order)
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()->role === 'admin', 403);
        $order->load('items.book','shipment','transactions');
        return view('orders.show', compact('order'));
    }

    public function showCheckout()
    {
        $items = CartItem::with('book')->where('user_id', auth()->id())->get();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('err','Your cart is empty.');
        }
        $subtotal = $items->sum(fn($i) => $i->quantity * ($i->book->price ?? 0));
        return view('orders.checkout', compact('items','subtotal'));
    }

    // Cart → Order (Make Payment)
    public function checkout(Request $req)
    {
        $req->validate([
            'payment_method'   => ['required','string','max:100'],
            'shipping_address' => ['required','string','max:2000'],
            'discount_amount'  => ['nullable','numeric','min:0'],
            'shipping_method'  => ['nullable','in:standard,express'], // optional if you submit it
            'shipping_amount'  => ['nullable','numeric','min:0'],     // from your hidden field
            'order_note'       => ['nullable','string','max:2000'],   // your input name
        ]);

        $manager  = app(PaymentManager::class);
        $strategy = $manager->resolve($req->payment_method);
        $strategy->validate($req);

        $userId = auth()->id();
        $items  = CartItem::with('book')->where('user_id', $userId)->get();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('err','Your cart is empty.');
        }

        $discount = (float) $req->input('discount_amount', 0);
        $shipping = (float) $req->input('shipping_amount', 0);

        $order = DB::transaction(function () use ($userId, $items, $req, $discount, $shipping, $strategy) {
            $subtotal = $items->sum(fn($i) => $i->quantity * ($i->book->price ?? 0));
            $total    = max(0, $subtotal + $shipping - $discount);

            $order = Order::create([
                'user_id'         => $userId,
                'order_date'      => now(),
                'status'          => Order::STATUS_PROCESSING,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'shipping_amount' => $shipping,
                'total_amount'    => $total,
                'payment_method'  => $req->payment_method,
                'notes'           => $req->input('order_note'), // map your field name to the column
            ]);

            // ---- write items + stock reservation (same as before) ----
            foreach ($items as $ci) {
                $book = Book::lockForUpdate()->find($ci->book_id);
                if (!$book) abort(400, "Book #{$ci->book_id} not found");
                if ($book->stock < $ci->quantity) abort(400, "Not enough stock for {$book->title}");

                OrderItem::create([
                    'order_id'   => $order->id,
                    'book_id'    => $book->id,
                    'quantity'   => $ci->quantity,
                    'unit_price' => $book->price,
                ]);

                $book->decrement('stock', $ci->quantity);

                DB::table('stock_movements')->insert([
                    'book_id' => $book->id,
                    'user_id' => $userId,
                    'type' => 'sale',
                    'quantity_change' => -$ci->quantity,
                    'reason' => 'Order #'.$order->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // shipment row (address)
            Shipment::create([
                'order_id' => $order->id,
                'shipping_address' => $req->shipping_address,
            ]);

            // charge full total (includes shipping)
            $strategy->charge($order, $total, $req->all());

            CartItem::where('user_id', $userId)->delete();

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('ok','Payment successful. Order created.');
    }


    // ===== State transitions (example endpoints) =====

    public function ship(Order $order)
    {
        $this->authorizeAdminOrOwner($order); // tailor to your policy/middleware
        $order->load('items','shipment');
        $order->state()->ship();
        return back()->with('ok', 'Order shipped');
    }

    public function arrive(Order $order)
    {
        $this->authorizeAdminOrOwner($order);
        $order->load('shipment');
        $order->state()->arrive();
        return back()->with('ok', 'Order marked as arrived');
    }

    public function complete(Order $order)
    {
        $this->authorizeAdminOrOwner($order);
        $order->state()->complete();
        return back()->with('ok', 'Order completed');
    }

    public function cancel(Order $order)
    {
        $this->authorizeAdminOrOwner($order);
        $order->load('items');
        $order->state()->cancel();
        return back()->with('ok', 'Order cancelled & refunded');
    }

    private function authorizeAdminOrOwner(Order $order): void
    {
        $user = auth()->user();
        if (! $user) abort(401);
        if ($user->role === 'admin') return;
        if ($order->user_id === $user->id) return;
        abort(403);
    }
}
