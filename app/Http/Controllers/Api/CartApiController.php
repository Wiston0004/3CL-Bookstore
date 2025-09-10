<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartAddRequest;
use App\Http\Resources\CartItemResource;
use App\Models\{CartItem, Book};
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function index(Request $request)
    {
        $items = CartItem::with('book')
            ->where('user_id', $request->user()->id)
            ->get();

        $subtotal = $items->sum(fn($i) => $i->quantity * ($i->book->price ?? 0));

        return response()->json([
            'data' => CartItemResource::collection($items),
            'meta' => ['subtotal' => (float) $subtotal]
        ]);
    }

    public function store(CartAddRequest $request)
    {
        $data = $request->validated();
        $userId = $request->user()->id;

        $book = Book::findOrFail($data['book_id']);
        if ($book->stock <= 0) {
            return response()->json(['message' => 'This book is currently out of stock.'], 422);
        }

        $item = CartItem::firstOrNew([
            'user_id' => $userId,
            'book_id' => $book->id,
        ]);

        $newQty = ($item->exists ? $item->quantity : 0) + (int) $data['quantity'];

        // stock cap (optional)
        if ($newQty > $book->stock) {
            return response()->json(['message' => 'Not enough stock available.'], 422);
        }

        $item->quantity = $newQty;
        $item->added_at = now();
        $item->save();

        return (new CartItemResource($item->load('book')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'quantity' => ['required','integer','min:1']
        ]);
        
        $stock = $cartItem->book?->stock ?? PHP_INT_MAX;
        if ($data['quantity'] > $stock) {
            return response()->json(['message' => 'Not enough stock available.'], 422);
        }

        $cartItem->update(['quantity' => (int) $data['quantity']]);

        return new CartItemResource($cartItem->load('book'));
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $cartItem->delete();
        return response()->json(['message' => 'Item removed.']);
    }
}
