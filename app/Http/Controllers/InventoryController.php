<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustStockRequest;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Web action: adjust stock via form submission
     */
    public function adjust(AdjustStockRequest $req, Book $book): RedirectResponse
    {
        $qty = (int) $req->quantity;
        if ($req->type === 'restock' && $qty < 0) $qty = abs($qty);
        if ($req->type === 'sale' && $qty > 0)  $qty = -abs($qty);

        DB::transaction(function () use ($book, $req, $qty) {
            $new = $book->stock + $qty;
            if ($new < 0) abort(422,'Insufficient stock');
            $book->update(['stock' => $new]);

            $book->stockMovements()->create([
                'user_id'         => auth()->id(),
                'type'            => $req->type,
                'quantity_change' => $qty,
                'reason'          => $req->reason,
            ]);
        });

        return back()->with('ok','Stock updated');
    }

    /**
     * API: GET /api/v1/inventory/{book}/stock
     */
    public function apiStock(Book $book)
    {
        return response()->json([
            'data' => [
                'book_id' => $book->id,
                'stock'   => (int) ($book->stock ?? 0),
            ],
        ]);
    }

    /**
     * API: POST /api/v1/inventory/adjust
     * Body JSON: { "book_id":1, "type":"restock|sale", "quantity":5, "reason":"Supplier" }
     */
    public function apiAdjust(Request $request)
    {
        $data = $request->validate([
            'book_id'  => ['required','integer','exists:books,id'],
            'type'     => ['required','in:restock,sale'],
            'quantity' => ['required','integer','min:1'],
            'reason'   => ['nullable','string','max:255'],
        ]);

        /** @var \App\Models\Book $book */
        $book = Book::findOrFail($data['book_id']);

        $qty = (int) $data['quantity'];
        if ($data['type'] === 'restock' && $qty < 0) $qty = abs($qty);
        if ($data['type'] === 'sale'    && $qty > 0) $qty = -abs($qty);

        DB::transaction(function () use ($book, $data, $qty) {
            $new = $book->stock + $qty;
            if ($new < 0) abort(422,'Insufficient stock');
            $book->update(['stock' => $new]);

            if (method_exists($book, 'stockMovements')) {
                $book->stockMovements()->create([
                    'user_id'         => auth()->id(),
                    'type'            => $data['type'],
                    'quantity_change' => $qty,
                    'reason'          => $data['reason'] ?? null,
                ]);
            }
        });

        $book->refresh();

        return response()->json([
            'ok'   => true,
            'data' => [
                'book_id' => $book->id,
                'stock'   => (int) $book->stock,
            ],
        ]);
    }
}