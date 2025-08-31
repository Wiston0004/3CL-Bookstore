<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Book;
use App\Models\Review;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:customer,staff,manager']);
    }

    public function store(StoreReviewRequest $request, Book $book)
    {
        Review::create([
            'book_id' => $book->id,
            'user_id' => auth()->id(),
            'rating'  => $request->validated()['rating'],
            'content' => $request->validated()['content'] ?? null,
        ]);

        return back()->with('ok', 'Thanks for your review!');
    }
}
