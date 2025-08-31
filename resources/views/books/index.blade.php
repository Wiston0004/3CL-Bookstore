@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">📚 Inventory — Books</h2>
@endsection

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

    @if(session('ok'))  <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('ok') }}</div> @endif
    @if(session('err')) <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">{{ session('err') }}</div> @endif

    <div class="flex items-center justify-between">
      <div class="text-sm text-gray-600">Manage books, adjust stock, filter low/out-of-stock.</div>
      @can('create', \App\Models\Book::class)
        <a href="{{ route('books.create') }}" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">+ Add Book</a>
      @endcan
    </div>

    <form method="get" class="bg-white p-4 rounded shadow grid grid-cols-1 md:grid-cols-5 gap-3" autocomplete="off">
      <input class="border rounded px-3 py-2" name="search" value="{{ request('search') }}" placeholder="Search title, author, ISBN">
      <select name="category_id" class="border rounded px-3 py-2">
        <option value="">All Categories</option>
        @foreach($categories as $c)
          <option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>
        @endforeach
      </select>
      <select name="stock" class="border rounded px-3 py-2">
        <option value="">Stock: All</option>
        <option value="low" @selected(request('stock')==='low')>Low (&le; 5)</option>
        <option value="out" @selected(request('stock')==='out')>Out of stock</option>
      </select>
      <select name="sort" class="border rounded px-3 py-2">
        @php $sort = request('sort','updated_at'); @endphp
        <option value="updated_at" @selected($sort==='updated_at')>Sort: Updated</option>
        <option value="title" @selected($sort==='title')>Title</option>
        <option value="id" @selected($sort==='id')>ID</option>
        <option value="stock" @selected($sort==='stock')>Stock</option>
      </select>
      <div class="flex gap-2">
        <select name="dir" class="border rounded px-3 py-2">
          @php $dir = request('dir','desc'); @endphp
          <option value="asc" @selected($dir==='asc')>Asc</option>
          <option value="desc" @selected($dir==='desc')>Desc</option>
        </select>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Apply</button>
        @if(request()->query())
          <a href="{{ route('books.index') }}" class="px-4 py-2 border rounded">Clear</a>
        @endif
      </div>
    </form>

    <div class="bg-white rounded shadow overflow-hidden">
      @php
        $q = request()->except(['page']);
        $toggleDir = request('dir','desc')==='desc' ? 'asc' : 'desc';
      @endphp

      @if($books->count() === 0)
        <div class="p-8 text-center text-gray-500">No books found. Try adjusting filters or add a new book.</div>
      @else
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left"><a href="{{ route('books.index', array_merge($q,['sort'=>'id','dir'=>$toggleDir])) }}" class="hover:underline">ID</a></th>
                <th class="px-4 py-3 text-left"><a href="{{ route('books.index', array_merge($q,['sort'=>'title','dir'=>$toggleDir])) }}" class="hover:underline">Title</a></th>
                <th class="px-4 py-3 text-left">Author</th>
                <th class="px-4 py-3 text-left">Category</th>
                <th class="px-4 py-3 text-left"><a href="{{ route('books.index', array_merge($q,['sort'=>'stock','dir'=>$toggleDir])) }}" class="hover:underline">Stock</a></th>
                <th class="px-4 py-3 text-left"><a href="{{ route('books.index', array_merge($q,['sort'=>'updated_at','dir'=>$toggleDir])) }}" class="hover:underline">Updated</a></th>
                <th class="px-4 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              @foreach($books as $b)
                <tr class="align-top">
                  <td class="px-4 py-3">{{ $b->id }}</td>
                  <td class="px-4 py-3">
                    <div class="font-medium">{{ $b->title }}</div>
                    <div class="text-xs text-gray-500">ISBN: {{ $b->isbn ?? '—' }}</div>
                  </td>
                  <td class="px-4 py-3">{{ $b->author ?? '—' }}</td>
                  <td class="px-4 py-3">
                    @if($b->categories->count())
                      <div class="flex flex-wrap gap-1">
                        @foreach($b->categories as $cat)
                          <span class="text-xs bg-gray-100 border rounded px-2 py-0.5">{{ $cat->name }}</span>
                        @endforeach
                      </div>
                    @else — @endif
                  </td>
                  <td class="px-4 py-3">
                    @php $stockClass = $b->stock == 0 ? 'text-red-700' : ($b->stock <= 5 ? 'text-amber-700' : 'text-gray-900'); @endphp
                    <span class="{{ $stockClass }}">{{ $b->stock }}</span>
                  </td>
                  <td class="px-4 py-3">{{ $b->updated_at?->format('Y-m-d H:i') }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                      @can('update', $b)
                        <a href="{{ route('books.edit',$b) }}" class="px-3 py-1.5 border rounded hover:bg-gray-50">Edit</a>
                        <a href="{{ route('inventory.history',$b) }}" class="px-3 py-1.5 border rounded hover:bg-gray-50">History</a>
                      @endcan
                      @can('delete', $b)
                        <form action="{{ route('books.destroy',$b) }}" method="POST" onsubmit="return confirm('Delete this book?')">
                          @csrf @method('DELETE')
                          <button class="px-3 py-1.5 border rounded text-red-700 hover:bg-red-50">Delete</button>
                        </form>
                      @endcan
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="p-4">{{ $books->onEachSide(1)->links() }}</div>
      @endif
    </div>

  </div>
</div>
@endsection
