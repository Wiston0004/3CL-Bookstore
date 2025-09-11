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
}