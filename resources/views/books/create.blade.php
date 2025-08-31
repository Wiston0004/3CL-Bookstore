@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Book</h2>
@endsection

@section('content')
<div class="py-6">
  <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded p-6">
      <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div>
          <label class="block text-sm font-medium mb-1">Title</label>
          <input name="title" value="{{ old('title') }}" class="w-full border rounded px-3 py-2" required>
          @error('title') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Author</label>
            <input name="author" value="{{ old('author') }}" class="w-full border rounded px-3 py-2">
            @error('author') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">ISBN</label>
            <input name="isbn" value="{{ old('isbn') }}" class="w-full border rounded px-3 py-2">
            @error('isbn') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Price (MYR)</label>
            <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="w-full border rounded px-3 py-2">
            @error('price') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Initial Stock</label>
            <input type="number" name="stock" value="{{ old('stock',0) }}" class="w-full border rounded px-3 py-2">
            @error('stock') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Description</label>
          <textarea name="description" class="w-full border rounded px-3 py-2" rows="4">{{ old('description') }}</textarea>
          @error('description') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Cover</label>
          <input type="file" name="cover" accept="image/*" class="block w-full text-sm">
          @error('cover') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Categories</label>
          <div class="grid grid-cols-2 gap-2">
            @foreach($categories as $c)
              <label class="flex items-center gap-2">
                <input type="checkbox" name="category_ids[]" value="{{ $c->id }}"> <span>{{ $c->name }}</span>
              </label>
            @endforeach
          </div>
          @error('category_ids') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
        </div>

        <div class="flex items-center justify-end gap-3">
          <a href="{{ route('books.index') }}" class="px-4 py-2 border rounded">Cancel</a>
          <button class="px-4 py-2 bg-indigo-600 text-white rounded">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
