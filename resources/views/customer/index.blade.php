@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">Books</h2>
@endsection

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <form method="get" class="mb-4">
      <input name="search" value="{{ request('search') }}" class="border rounded px-3 py-2" placeholder="Search books...">
      <button class="px-3 py-2 bg-indigo-600 text-white rounded">Search</button>
    </form>

    @if($books->count()===0)
      <div class="bg-white p-6 rounded shadow text-gray-600">No books found.</div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($books as $b)
          <a href="{{ route('customer.show',$b) }}" class="bg-white rounded shadow p-4 hover:shadow-md">
            @if($b->cover_path)
              <img src="{{ asset('storage/'.$b->cover_path) }}" class="w-full h-56 object-cover rounded mb-2">
            @endif
            <div class="font-semibold">{{ $b->title }}</div>
            <div class="text-sm text-gray-600">{{ $b->author }}</div>
            <div class="mt-2">RM {{ number_format($b->price,2) }}</div>
          </a>
        @endforeach
      </div>
      <div class="mt-4">{{ $books->links() }}</div>
    @endif
  </div>
</div>
@endsection
