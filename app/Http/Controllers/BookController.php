<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUpdateBookRequest;
use App\Models\Book;
use App\Models\Category;
use App\Services\CatalogFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // 👈 THIS is the correct import
/**
 * @method void authorize($ability, $arguments = [])
 */
class BookController extends Controller
{
    public function __construct(private CatalogFacade $catalog) {} 

    public function index(Request $r)
    {
        // UI controls
        $threshold = (int) $r->input('low', 5);
        $q = trim((string) $r->input('q', ''));

        $books = Book::with(['categories','tags'])
            ->when($r->search, fn($q,$s) => $q->where(fn($w) => $w
                ->where('title','like',"%$s%")
                ->orWhere('author','like',"%$s%")
                ->orWhere('isbn','like',"%$s%")))
            ->latest()
            ->paginate(12);
        
        // Stats (global by threshold; independent of current page)
        $stats = [
            'total' => Book::count(),
            'low'   => Book::where('stock', '<',  $threshold)->count(),
            'out'   => Book::where('stock', '<=', 0)->count(),
        ];

        return view('books.index', compact('books','threshold','stats'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id','name']);
        return view('books.create', compact('categories'));
    }

    public function store(StoreUpdateBookRequest $req)
    {
        $book = $this->catalog->createBook(
            $req->validated() + ['cover_image' => $req->file('cover_image')]
        );

        return redirect()->route('books.show', $book)->with('ok','Book created');
    }

    public function show(Book $book)
    {
        $book->load(['categories','tags','reviews' => fn($q) => $q->latest()]);
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $categories = Category::orderBy('name')->get(['id','name']);
        $book->load('categories');
        return view('books.edit', compact('book','categories'));
    }

    public function update(StoreUpdateBookRequest $req, Book $book)
    {
        $this->catalog->updateBook(
            $book,
            $req->validated() + ['cover_image' => $req->file('cover_image')]
        );

        return redirect()->route('books.show',$book)->with('ok','Book updated');
    }

    public function destroy(Book $book)
    {
        $book->delete(); // soft delete
        return redirect()->route('books.index')->with('ok','Book discontinued');
    }

    public function customerIndex()
    {
        $books = \App\Models\Book::with('categories')
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(12);

        return view('customer.index', compact('books'));
    }

    public function customerShow(Book $book)
    {
        // ensure not deleted
        abort_if(!is_null($book->deleted_at), 404);

        $book->load(['categories','reviews' => fn($q) => $q->latest()]);
        return view('customer.show', compact('book'));
    }

     /**
     * API: GET /api/v1/books
     */
    public function apiIndex(Request $request)
    {
        $q = \App\Models\Book::query()->with('categories');

        if ($search = trim((string) $request->query('q', ''))) {
            $q->where(function ($w) use ($search) {
                $w->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($cat = $request->query('category_id')) {
            $q->whereHas('categories', fn($w) => $w->where('categories.id', (int) $cat));
        }

        $books = $q->latest()->paginate(12);
        return response()->json([
            'data' => $books->getCollection()->map(function (\App\Models\Book $b) {
                return [
                    'id'         => $b->id,
                    'title'      => $b->title,
                    'author'     => $b->author,
                    'isbn'       => $b->isbn,
                    'price'      => $b->price,
                    'stock'      => $b->stock ?? null,
                    'categories' => $b->categories->pluck('name'),
                ];
            })->values(),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page'    => $books->lastPage(),
                'total'        => $books->total(),
            ],
        ]);
    }

    /**
     * API: GET /api/v1/books/{book}
     */
    public function apiShow(Book $book)
    {
        $book->load([
            'categories:id,name',
            'reviews' => fn($r) => $r->latest()->limit(5),
        ]);

        return response()->json([
            'data' => [
                'id'         => $book->id,
                'title'      => $book->title,
                'author'     => $book->author,
                'isbn'       => $book->isbn,
                'price'      => $book->price,
                'stock'      => $book->stock ?? null,
                'categories' => $book->categories->pluck('name'),
                'reviews'    => $book->reviews->map(fn($rv) => [
                    'user_id' => $rv->user_id,
                    'rating'  => $rv->rating,
                    'content' => $rv->content,
                    'date'    => optional($rv->created_at)->toDateTimeString(),
                ]),
            ],
        ]);
    }

    /**
     * API: POST /api/v1/books
     * Create a new book (only staff/manager)
     */
    public function apiStore(Request $request)
    {
        if (!$this->checkUserRole($request, ['staff'])) {
            return response()->json(['error' => 'Forbidden – only staff/manager can add books'], 403);
        }

        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'author'   => 'required|string|max:255',
            'isbn'     => 'required|string|max:50|unique:books,isbn',
            'price'    => 'required|numeric|min:0',
            'stock'    => 'required|integer|min:0',
            'category_ids' => 'array'
        ]);

        $book = Book::create($data);

        if (!empty($data['category_ids'])) {
            $book->categories()->sync($data['category_ids']);
        }

        return response()->json(['data' => $book], 201);
    }

    /**
     * API: PUT /api/v1/books/{book}
     * Update a book (only staff/manager)
     */
    public function apiUpdate(Request $request, Book $book)
    {
        if (!$this->checkUserRole($request, ['staff'])) {
            return response()->json(['error' => 'Forbidden – only staff/manager can update books'], 403);
        }

        $data = $request->validate([
            'title'    => 'sometimes|string|max:255',
            'author'   => 'sometimes|string|max:255',
            'isbn'     => "sometimes|string|max:50|unique:books,isbn,{$book->id}",
            'price'    => 'sometimes|numeric|min:0',
            'stock'    => 'sometimes|integer|min:0',
            'category_ids' => 'array'
        ]);

        $book->update($data);

        if (isset($data['category_ids'])) {
            $book->categories()->sync($data['category_ids']);
        }

        return response()->json(['data' => $book]);
    }

    /**
     * API: DELETE /api/v1/books/{book}
     * Delete a book (only staff/manager)
     */
    public function apiDestroy(Request $request, Book $book)
    {
        if (!$this->checkUserRole($request, ['staff'])) {
            return response()->json(['error' => 'Forbidden – only staff/manager can delete books'], 403);
        }

        $book->delete();
        return response()->json(['message' => 'Book deleted']);
    }

    /**
     * 🔑 Helper: Verify role using User Management API
     */
    private function checkUserRole(Request $request, array $allowedRoles): bool
    {
        $user = $request->user(); // Sanctum resolves the user model from the token

        if (!$user) {
            return false;
        }

        return in_array($user->role ?? '', $allowedRoles);
    }

}

