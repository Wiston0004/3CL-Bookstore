<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Book;
use App\Models\Review;
use App\Services\PurchasesApi;        // <-- add
use Illuminate\Support\Facades\DB;    // <-- add

class ReviewController extends Controller
{
    public function __construct()
    {
    $this->middleware(['auth','role:customer,staff,manager'])
         ->except('apiRatingsSummary');
    }

    /**
     * CONSUMES teammate's Orders/Customer API before accepting a review.
     * Blocks only when the API explicitly says "not purchased".
     * If the API is unreachable (timeout/error), we fail open (allow) to avoid UX breakage.
     */
    public function store(StoreReviewRequest $request, Book $book)
    {
        $userId = (int) auth()->id();
        $okToReview = null;

        try {
            /** @var PurchasesApi $client */
            $client = app(PurchasesApi::class);
            $okToReview = $client->hasPurchased($userId, $book->id); // true|false|null
        } catch (\Throwable $e) {
            // Never crash user flow if the remote service is down
            $okToReview = null;
        }

        if ($okToReview === false) {
            return back()->with('err', 'You can only review books you have purchased.');
        }

        Review::create([
            'book_id' => $book->id,
            'user_id' => $userId,
            'rating'  => $request->validated()['rating'],
            'content' => $request->validated()['content'] ?? null,
        ]);

        return back()->with('ok', 'Thanks for your review!');
    }

    /**
     * EXPOSES a small web service for analytics/recommendations:
     * GET /api/v1/books/{book}/ratings -> JSON { book_id, count, avg, breakdown }
     *
     * If you prefer a separate Api controller, you can move this method out.
     */
    public function apiRatingsSummary(Book $book)
    {
        $agg = Review::where('book_id', $book->id)
            ->selectRaw('COUNT(*) as count, AVG(rating) as avg')
            ->first();

        $breakdown = Review::where('book_id', $book->id)
            ->select('rating', DB::raw('COUNT(*) as c'))
            ->groupBy('rating')->pluck('c','rating');

        return response()->json([
            'data' => [
                'book_id'   => $book->id,
                'count'     => (int) ($agg->count ?? 0),
                'avg'       => $agg->avg ? round((float)$agg->avg, 2) : 0.0,
                'breakdown' => (object) $breakdown, // {5:10,4:3,...}
            ]
        ]);
    }
}