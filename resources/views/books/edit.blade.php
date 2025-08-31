@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Book — {{ $book->title }}</h2>
@endsection

@section('content')
<div class="py-6">
  <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

    @if(session('ok'))  <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">{{ session('ok') }}</div> @endif
    @if(session('err')) <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">{{ session('err') }}</div> @endif

    <div class="bg-white shadow rounded p-6">
      <form action="{{ route('books.update',$book) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf @method('PUT')

        <div>
          <label class="block text-sm font-medium mb-1">Title</label>
          <input name="title" value="{{ old('title', $book->title) }}" class="w-full border rounded px-3 py-2" required>
          @error('title') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Author</label>
            <input name="author" value="{{ old('author', $book->author) }}" class="w-full border rounded px-3 py-2">
            @error('author') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">ISBN</label>
            <input name="isbn" value="{{ old('isbn',$book->isbn) }}" class="w-full border rounded px-3 py-2">
            @error('isbn') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Price (MYR)</label>
            <input type="number" step="0.01" name="price" value="{{ old('price', $book->price) }}" class="w-full border rounded px-3 py-2">
            @error('price') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Stock</label>
            <input type="number" name="stock" value="{{ old('stock', $book->stock) }}" class="w-full border rounded px-3 py-2">
            @error('stock') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Description</label>
          <textarea name="description" class="w-full border rounded px-3 py-2" rows="4">{{ old('description',$book->description) }}</textarea>
          @error('description') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Cover</label>
            <input type="file" name="cover" accept="image/*" class="block w-full text-sm">
            @error('cover') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
            @if($book->cover_path)
              <div class="mt-2"><img src="{{ asset('storage/'.$book->cover_path) }}" class="w-28 h-36 object-cover rounded"></div>
            @endif
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Categories</label>
            <div class="grid grid-cols-2 gap-2">
              @foreach($categories as $c)
                <label class="flex items-center gap-2">
                  <input type="checkbox" name="category_ids[]" value="{{ $c->id }}" {{ $book->categories->pluck('id')->contains($c->id) ? 'checked' : '' }}>
                  <span>{{ $c->name }}</span>
                </label>
              @endforeach
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3">
          <a href="{{ route('books.index') }}" class="px-4 py-2 border rounded">Back</a>
          <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
        </div>
      </form>
    </div>

    {{-- Stock Adjustment --}}
    <div class="bg-white shadow rounded p-6">
      <h3 class="font-semibold mb-2">Adjust Stock (Current: {{ $book->stock }})</h3>
      <form action="{{ route('inventory.adjust', $book) }}" method="POST" class="flex flex-col md:flex-row gap-3 items-end">
        @csrf
        <div>
          <label class="block text-sm">Type</label>
          <select name="type" class="border rounded px-3 py-2">
            <option value="restock">Restock (+)</option>
            <option value="sale">Sale (-)</option>
            <option value="adjustment">Adjustment (+/-)</option>
          </select>
        </div>
        <div>
          <label class="block text-sm">Quantity</label>
          <input type="number" name="quantity" class="border rounded px-3 py-2" required>
        </div>
        <div class="flex-1">
          <label class="block text-sm">Reason (optional)</label>
          <input name="reason" class="border rounded px-3 py-2 w-full" placeholder="Supplier delivery, shrinkage, audit correction">
        </div>
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">Update</button>
        <a class="px-3 py-2 border rounded" href="{{ route('inventory.history',$book) }}">View History</a>
      </form>
    </div>

  </div>
</div>
@endsection
