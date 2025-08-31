<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUpdateBookRequest;
use App\Models\Book;
use App\Models\Category;
use App\Services\CatalogFacade;
use Illuminate\Http\Request;

/**
 * @method void authorize($ability, $arguments = [])
 */

class BookController extends Controller
{
    public function __construct(private CatalogFacade $catalog) {}

    /* ===== CUSTOMER PAGES ===== */
    public function customerIndex(Request $r)
    {
        $q = Book::query()->with('categories')->whereNull('deleted_at');

        if ($s = trim($r->input('search',''))) {
            $q->where(fn($x) => $x->where('title','like',"%$s%")
                                   ->orWhere('author','like',"%$s%")
                                   ->orWhere('isbn','like',"%$s%"));
        }

        $books = $q->orderBy('title')->paginate(12)->withQueryString();
        return view('customer.index', compact('books'));
    }

    public function customerShow(Book $book)
    {
        abort_if(!is_null($book->deleted_at), 404);
        $book->load(['categories','reviews' => fn($q) => $q->latest()]);
        return view('customer.show', compact('book'));
    }

    /* ===== STAFF / MANAGER PAGES ===== */
    public function index(Request $r)
    {
        $this->authorize('viewAny', Book::class);

        $q = Book::query()->with('categories');

        if ($cat = $r->integer('category_id')) {
            $q->whereHas('categories', fn($x) => $x->where('categories.id', $cat));
        }

        if ($stock = $r->input('stock')) {
            if ($stock === 'low') $q->where('stock', '>', 0)->where('stock', '<=', 5);
            if ($stock === 'out') $q->where('stock', 0);
        }

        if ($s = trim($r->input('search',''))) {
            $q->where(fn($x) => $x->where('title','like',"%$s%")
                                   ->orWhere('author','like',"%$s%")
                                   ->orWhere('isbn','like',"%$s%"));
        }

        $allowedSort = ['id','title','stock','updated_at'];
        $sort = in_array($r->input('sort'), $allowedSort, true) ? $r->input('sort') : 'updated_at';
        $dir  = $r->input('dir') === 'asc' ? 'asc' : 'desc';

        $books = $q->orderBy($sort, $dir)->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get(['id','name']);

        return view('books.index', compact('books','categories','sort','dir'));
    }

    public function create()
    {
        $this->authorize('create', Book::class);
        $categories = Category::orderBy('name')->get(['id','name']);
        return view('books.create', compact('categories'));
    }

    public function store(StoreUpdateBookRequest $request)
    {
        $this->authorize('create', Book::class);
        $book = $this->catalog->createBook($request->validated());
        return redirect()->route('books.edit', $book)->with('ok', 'Book created.');
    }

    public function edit(Book $book)
    {
        $this->authorize('update', $book);
        $categories = Category::orderBy('name')->get(['id','name']);
        $book->load('categories');
        return view('books.edit', compact('book','categories'));
    }

    public function update(StoreUpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);
        $this->catalog->createBook(array_merge($request->validated(), ['id' => $book->id]));
        return back()->with('ok', 'Book updated.');
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);
        $book->delete();
        return redirect()->route('books.index')->with('ok','Book moved to trash.');
    }
}
