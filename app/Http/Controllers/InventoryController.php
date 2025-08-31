<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustStockRequest;
use App\Models\Book;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * @method void middleware($middleware, array $options = [])
 * @method void authorize($ability, $arguments = [])
 */

class InventoryController extends Controller
{
    public function __construct()
    {
        // Routes already use auth+role, this is defense-in-depth.
        $this->middleware(['auth','role:staff,manager']);
    }

    public function adjust(AdjustStockRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $data   = $request->validated();
        $type   = $data['type'];            // restock|sale|adjustment
        $delta  = (int) $data['quantity'];  // already normalized in the request
        $reason = $data['reason'] ?? null;

        try {
            DB::transaction(function () use ($book, $type, $delta, $reason) {
                $locked = Book::query()->whereKey($book->id)->lockForUpdate()->firstOrFail();
                $newStock = $locked->stock + $delta;

                if ($newStock < 0) {
                    abort(422, 'Insufficient stock: would result in negative inventory.');
                }

                $locked->update(['stock' => $newStock]);

                /** @var \Illuminate\Contracts\Auth\Guard $guard */
                $guard = auth(); // Intelephense hint for ->id()
                StockMovement::create([
                    'book_id'         => $locked->id,
                    'user_id'         => $guard->id(),
                    'type'            => $type,
                    'quantity_change' => $delta,
                    'resulting_stock' => $newStock,
                    'reason'          => $reason,
                ]);
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('err', $e->getMessage() ?: 'Failed to adjust stock.');
        }

        $msg = match ($type) {
            'restock'    => "Restocked by +{$delta}.",
            'sale'       => "Recorded sale: {$delta}.",
            'adjustment' => "Adjusted stock by {$delta}.",
        };
        return back()->with('ok', $msg);
    }

    public function history(Book $book)
    {
        $this->authorize('view', $book);

        $book->load([
            'stockMovements' => fn($q) => $q->latest(),
            'stockMovements.user:id,name',
            'categories:id,name',
        ]);

        return view('books.history', compact('book'));
    }
}
