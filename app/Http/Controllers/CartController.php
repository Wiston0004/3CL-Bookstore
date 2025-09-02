<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartAddRequest;
use App\Models\{CartItem, Book};
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $items = CartItem::with('book')
            ->where('user_id', auth()->id())
            ->get();

        $subtotal = $items->sum(fn($i) => $i->quantity * ($i->book->price ?? 0));
        return view('cart.index', compact('items','subtotal'));
    }

    public function add(CartAddRequest $request)
    {
        $data = $request->validated();
        $book = Book::findOrFail($data['book_id']);

        if ($book->stock <= 0) {
            return back()->with('err', 'This book is currently out of stock.');
        }

        $item = CartItem::firstOrNew([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
        ]);

        $newQty = ($item->exists ? $item->quantity : 0) + (int)$data['quantity'];

        // optional stock cap
        if ($newQty > $book->stock) {
            return back()->with('err', 'Not enough stock available.');
        }

        $item->quantity = $newQty;
        $item->added_at = now();
        $item->save();

        return back()->with('ok', 'Added to cart!');
    }

    // NEW: update quantity
    public function update(Request $request, CartItem $cartItem)
    {
        // ownership check
        if ($cartItem->user_id !== auth()->id()) abort(403);

        $data = $request->validate(['quantity' => ['required','integer','min:1']]);

        // optional stock cap
        $stock = $cartItem->book?->stock ?? PHP_INT_MAX;
        if ($data['quantity'] > $stock) {
            return back()->with('err', 'Not enough stock available.');
        }

        $cartItem->update(['quantity' => (int)$data['quantity']]);

        return back()->with('ok', 'Quantity updated.');
    }

    // NEW: remove item
    public function remove(CartItem $cartItem)
    {
        if ($cartItem->user_id !== auth()->id()) abort(403);

        $cartItem->delete();

        return back()->with('ok', 'Item removed.');
    }
}
